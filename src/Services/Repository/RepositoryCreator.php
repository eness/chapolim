<?php

namespace Eliezer\Chapolim\Services\Repository;

use Eliezer\Chapolim\Services\Creator;
use Illuminate\Support\Str;

class RepositoryCreator extends Creator
{

    public function create($name, $module = null, $model = null, $ormFolder = null, $force = false)
    {
        $ormFolder = Str::studly($ormFolder ?? 'Eloquent');
        
        if (! $force)
            $this->ensureClassDoesntAlreadyExist($name, $this->getRepositoryPath($module, $ormFolder));        
        
        $stub = $this->getStub($model);
        $path = $this->getPath($name, $this->getRepositoryPath($module, $ormFolder));

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path, $this->populateStub($name, $module, $stub, $model, $ormFolder)
        );

        return $path;
    }

    /**
     * Get the repository stub file.
     *
     * @param  string|null  $model
     * @param  bool  $create
     * @return string
     */
    protected function getStub($model)
    {
        if (is_null($model)) {
            $stub = $this->stubPath().'/repository.stub';
        } else {
            $stub = $this->stubPath().'/repository.model.stub';
        }

        return $this->files->get($stub);
    }

    /**
     * Populate the place-holders in the model stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @param  string|null  $table
     * @return string
     */
    protected function populateStub($name, $module, $stub, $model, $ormFolder)
    {
        $stub = str_replace(
            ['DummyNamespace', '{{ namespace }}', '{{namespace}}'],
            $this->getNamespace($module, $ormFolder), $stub
        );

        $stub = str_replace(
            ['DummyClass', '{{ class }}', '{{class}}'],
            $this->getClassName($name), $stub
        );

        $stub = str_replace(
            ['DummyInterface', '{{ interface }}', '{{interface}}'],
            $this->getInterfaceClassName($name), $stub
        );

        if (! is_null($model)) {
            $stub = str_replace(
                ['DummyModel', '{{ model }}', '{{model}}'],
                $model, $stub
            );
        }

        return $stub;
    }

    /**
     * Get the class namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($module, $ormFolder)
    {
        return is_null($module)
            ? 'App\Repositories\\' . $ormFolder
            : 'Modules\\' . Str::studly($module) . '\Repositories' . $ormFolder;
    }

    /**
     * Get the class name of a class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getInterfaceClassName($name)
    {
        return $this->getClassName($name) . 'Interface';
    }

    /**
     * Get path to the class.
     *
     * @param  string  $name
     * @param  string  $path
     * @return string
     */
    protected function getRepositoryPath($module, $ormFolder)
    {
        if (! is_null($module)) {
            return base_path('modules/' . $module . '/Repositories\/' . $ormFolder);
        }

        return app_path('Repositories/' . $ormFolder);
    }
    
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }
}