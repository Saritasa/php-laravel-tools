<?php

namespace Saritasa\LaravelTools\Factories;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Table;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Saritasa\LaravelTools\Database\SchemaReader;
use Saritasa\LaravelTools\DTO\ClassFactoryConfig;
use Saritasa\LaravelTools\DTO\ModelBasedClassFactoryConfig;
use Saritasa\LaravelTools\Services\TemplateWriter;

/**
 * Factory to scaffold some new class based on template and existing model class.
 */
abstract class ModelBasedClassFactory extends ClassFactory
{
    /**
     * Factory configuration.
     *
     * @var ModelBasedClassFactoryConfig
     */
    protected $config;

    /**
     * Database table information reader.
     *
     * @var SchemaReader
     */
    protected $schemaReader;

    /**
     * Target model's table details.
     *
     * @var Table
     */
    protected $table;

    /**
     * Target model's table columns.
     *
     * @var Column[]
     */
    protected $columns;

    /**
     * Target model's foreign keys.
     *
     * @var ForeignKeyConstraint[]
     */
    protected $foreignKeys;

    /**
     * Factory to scaffold some new class based on template.
     *
     * @param TemplateWriter $templateWriter Templates files writer
     * @param SchemaReader $schemaReader Database table information reader
     */
    public function __construct(TemplateWriter $templateWriter, SchemaReader $schemaReader)
    {
        parent::__construct($templateWriter);

        $this->schemaReader = $schemaReader;
    }

    /**
     * Build and write new class file.
     *
     * @return string Result file name
     * @throws Exception
     * @throws FileNotFoundException
     */
    public function build(): string
    {
        $this->readTableInformation($this->getTableName());

        $filledPlaceholders = $this->getPlaceHoldersValues();

        $this->templateWriter
            ->take($this->config->templateFilename)
            ->fill($filledPlaceholders)
            ->write($this->config->resultFilename);

        return $this->config->resultFilename;
    }

    /**
     * Read model's table information.
     *
     * @param string $tableName Table name to retrieve information for
     *
     * @return void
     */
    private function readTableInformation(string $tableName): void
    {
        $this->table = $this->schemaReader->getTableDetails($tableName);

        $this->foreignKeys = [];
        foreach ($this->table->getForeignKeys() as $foreignKey) {
            $localColumn = $foreignKey->getLocalColumns()[0];
            $this->foreignKeys[$localColumn] = $foreignKey;
        };

        $this->columns = [];
        foreach ($this->table->getColumns() as $column) {
            if (!in_array($column->getName(), $this->config->excludedAttributes)) {
                $this->columns[$column->getName()] = $column;
            }
        }
    }

    /**
     * Returns model's table name for which need to build new class.
     *
     * @return string
     * @see configure method for details
     */
    private function getTableName(): string
    {
        /**
         * Model for which need to build new class.
         *
         * @var Model $model
         */
        $model = new $this->config->modelClassName();

        return $model->getTable();
    }

    /**
     * Configure factory to build new class.
     *
     * @param ModelBasedClassFactoryConfig|ClassFactoryConfig $config Class configuration
     *
     * @return static
     */
    public function configure($config)
    {
        $this->config = $config;

        return $this;
    }
}
