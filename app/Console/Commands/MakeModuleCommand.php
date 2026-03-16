<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
                            {name : Module name, e.g. Product}
                            {--fields= : Example: name:string,sku:string,price:decimal,stock:int,description:text?}
                            {--test : Generate tests}
                            {--migration : Generate migration}';

    protected $description = 'Generate a Clean Architecture + CQRS Module';

    public function handle(): int
    {
        $module = Str::studly($this->argument('name'));
        $moduleVar = Str::camel($module);
        $modulePlural = Str::pluralStudly($module);
        $modulePluralSnake = Str::snake($modulePlural);

        $fields = $this->parseFields((string) $this->option('fields'));

        if (empty($fields)) {
            $fields = [
                ['name' => 'name', 'type' => 'string', 'nullable' => false],
            ];
        }

        $basePaths = [
            app_path("Domain/{$module}/Entities"),
            app_path("Domain/{$module}/Contracts"),

            app_path("Application/{$module}/DTOs"),
            app_path("Application/{$module}/Commands/Create{$module}"),
            app_path("Application/{$module}/Commands/Update{$module}"),
            app_path("Application/{$module}/Commands/Delete{$module}"),
            app_path("Application/{$module}/Queries/Get{$module}ById"),
            app_path("Application/{$module}/Queries/List{$modulePlural}"),

            app_path("Infrastructure/Persistence/Eloquent/Models"),
            app_path("Infrastructure/Persistence/Eloquent/Repositories"),

            app_path("Presentation/Http/Controllers/Api"),
            app_path("Presentation/Http/Controllers/Web"),
            app_path("Presentation/Http/Requests/{$module}"),
            app_path("Presentation/Views/{$modulePluralSnake}"),
        ];

        if ($this->option('test')) {
            $basePaths[] = base_path("tests/Feature/Api");
            $basePaths[] = base_path("tests/Feature/Web");
            $basePaths[] = base_path("tests/Unit/{$module}");
        }

        foreach ($basePaths as $path) {
            File::ensureDirectoryExists($path);
        }

        $files = [
            app_path("Domain/{$module}/Entities/{$module}.php")
                => $this->stubEntity($module, $fields),

            app_path("Domain/{$module}/Contracts/{$module}Repository.php")
                => $this->stubRepositoryContract($module),

            app_path("Application/{$module}/DTOs/{$module}DTO.php")
                => $this->stubDto($module, $fields),

            app_path("Application/{$module}/DTOs/Paged{$modulePlural}DTO.php")
                => $this->stubPagedDto($module, $modulePlural),

            app_path("Application/{$module}/DTOs/Create{$module}DTO.php")
                => $this->stubCreateDto($module, $fields),

            app_path("Application/{$module}/DTOs/Update{$module}DTO.php")
                => $this->stubUpdateDto($module, $fields),

            app_path("Application/{$module}/Commands/Create{$module}/Create{$module}Command.php")
                => $this->stubCreateCommand($module),

            app_path("Application/{$module}/Commands/Create{$module}/Create{$module}CommandHandler.php")
                => $this->stubCreateCommandHandler($module, $fields),

            app_path("Application/{$module}/Commands/Update{$module}/Update{$module}Command.php")
                => $this->stubUpdateCommand($module),

            app_path("Application/{$module}/Commands/Update{$module}/Update{$module}CommandHandler.php")
                => $this->stubUpdateCommandHandler($module, $fields),

            app_path("Application/{$module}/Commands/Delete{$module}/Delete{$module}Command.php")
                => $this->stubDeleteCommand($module),

            app_path("Application/{$module}/Commands/Delete{$module}/Delete{$module}CommandHandler.php")
                => $this->stubDeleteCommandHandler($module),

            app_path("Application/{$module}/Queries/Get{$module}ById/Get{$module}ByIdQuery.php")
                => $this->stubGetByIdQuery($module),

            app_path("Application/{$module}/Queries/Get{$module}ById/Get{$module}ByIdQueryHandler.php")
                => $this->stubGetByIdQueryHandler($module, $fields),

            app_path("Application/{$module}/Queries/List{$modulePlural}/List{$modulePlural}Query.php")
                => $this->stubListQuery($module, $modulePlural),

            app_path("Application/{$module}/Queries/List{$modulePlural}/List{$modulePlural}QueryHandler.php")
                => $this->stubListQueryHandler($module, $modulePlural, $fields),

            app_path("Infrastructure/Persistence/Eloquent/Models/{$module}Model.php")
                => $this->stubModel($module, $modulePluralSnake, $fields),

            app_path("Infrastructure/Persistence/Eloquent/Repositories/Eloquent{$module}Repository.php")
                => $this->stubRepositoryImpl($module, $fields),

            app_path("Presentation/Http/Requests/{$module}/Store{$module}Request.php")
                => $this->stubStoreRequest($module, $fields),

            app_path("Presentation/Http/Requests/{$module}/Update{$module}Request.php")
                => $this->stubUpdateRequest($module, $fields),

            app_path("Presentation/Http/Controllers/Api/{$module}ApiController.php")
                => $this->stubApiController($module, $modulePlural, $fields),

            app_path("Presentation/Http/Controllers/Web/{$module}WebController.php")
                => $this->stubWebController($module, $modulePlural, $modulePluralSnake, $fields),

            app_path("Presentation/Views/{$modulePluralSnake}/index.blade.php")
                => $this->stubIndexView($module, $modulePlural, $modulePluralSnake, $fields),

            app_path("Presentation/Views/{$modulePluralSnake}/create.blade.php")
                => $this->stubCreateView($module, $modulePluralSnake, $fields),

            app_path("Presentation/Views/{$modulePluralSnake}/show.blade.php")
                => $this->stubShowView($module, $moduleVar, $fields),

            app_path("Presentation/Views/{$modulePluralSnake}/edit.blade.php")
                => $this->stubEditView($module, $moduleVar, $modulePluralSnake, $fields),

            app_path("Presentation/Routes/{$modulePluralSnake}.php")
                => $this->stubRoutes($module, $modulePluralSnake),
        ];

        if ($this->option('test')) {
            $files[base_path("tests/Feature/Api/{$module}ApiTest.php")] =
                $this->stubFeatureApiTest($module, $modulePluralSnake, $fields);

            $files[base_path("tests/Feature/Web/{$module}WebTest.php")] =
                $this->stubFeatureWebTest($module, $modulePlural, $modulePluralSnake, $fields);

            $files[base_path("tests/Unit/{$module}/Create{$module}CommandHandlerTest.php")] =
                $this->stubUnitCreateHandlerTest($module, $fields);
        }

        if ($this->option('migration')) {
            $migrationPath = database_path('migrations/' . date('Y_m_d_His') . '_create_' . $modulePluralSnake . '_table.php');
            $files[$migrationPath] = $this->stubMigration($modulePluralSnake, $fields);
        }

        foreach ($files as $path => $content) {
            if (File::exists($path)) {
                $this->warn("Skip, already exists: {$path}");
                continue;
            }

            File::ensureDirectoryExists(dirname($path));
            File::put($path, $content);
            $this->info("Created: {$path}");
        }

        $this->newLine();
        $this->info("Module {$module} generated successfully.");
        $this->warn('Next steps:');
        $this->line("1. Tambahkan binding repository di CQRSServiceProvider.php");
        $this->line("2. Copy routes dari app/Presentation/Routes/{$modulePluralSnake}.php");
        if ($this->option('migration')) {
            $this->line("3. Jalankan php artisan migrate");
        } else {
            $this->line("3. Buat migration untuk tabel {$modulePluralSnake}");
        }
        if ($this->option('test')) {
            $this->line("4. Review test auth/API sesuai kebutuhan project kamu");
        }
        $this->line("5. Sesuaikan rule business, unique check, dan field relation bila perlu");

        return self::SUCCESS;
    }

    protected function parseFields(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $items = array_filter(array_map('trim', explode(',', $raw)));
        $fields = [];

        foreach ($items as $item) {
            [$name, $type] = array_pad(explode(':', $item, 2), 2, 'string');

            $name = trim($name);
            $type = trim($type);

            if ($name === '') {
                continue;
            }

            $nullable = false;
            if (str_ends_with($type, '?')) {
                $nullable = true;
                $type = substr($type, 0, -1);
            }

            $type = strtolower($type);

            if (!in_array($type, ['string', 'text', 'int', 'integer', 'decimal', 'float', 'bool', 'boolean', 'date', 'datetime'], true)) {
                $type = 'string';
            }

            $fields[] = [
                'name' => Str::snake($name),
                'type' => $type,
                'nullable' => $nullable,
            ];
        }

        return $fields;
    }

    protected function phpType(array $field, bool $withNullable = false): string
    {
        $map = [
            'string' => 'string',
            'text' => 'string',
            'int' => 'int',
            'integer' => 'int',
            'decimal' => 'float',
            'float' => 'float',
            'bool' => 'bool',
            'boolean' => 'bool',
            'date' => 'string',
            'datetime' => 'string',
        ];

        $type = $map[$field['type']] ?? 'string';

        if ($withNullable && $field['nullable']) {
            return '?' . $type;
        }

        return $type;
    }

    protected function isNullable(array $field): bool
    {
        return (bool) $field['nullable'];
    }

    protected function constructorParams(array $fields, bool $includeId = false): string
    {
        $lines = [];

        if ($includeId) {
            $lines[] = "        public readonly ?int \$id,";
        }

        foreach ($fields as $field) {
            $type = $this->phpType($field, true);
            $prefix = $includeId ? '        public ' : '        public readonly ';
            $lines[] = "{$prefix}{$type} \${$field['name']},";
        }

        return implode("\n", $lines);
    }

    protected function dtoConstructorParams(array $fields, bool $includeId = false): string
    {
        $lines = [];

        if ($includeId) {
            $lines[] = "        public readonly int \$id,";
        }

        foreach ($fields as $field) {
            $type = $this->phpType($field, true);
            $lines[] = "        public readonly {$type} \${$field['name']},";
        }

        return implode("\n", $lines);
    }

    protected function entityArgsFromData(array $fields): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $lines[] = "            {$field['name']}: \$data->{$field['name']},";
        }
        return implode("\n", $lines);
    }

    protected function dtoArgsFromObject(array $fields, string $objectVar): string
    {
        $lines = ["            {$objectVar}->id,"];
        foreach ($fields as $field) {
            $lines[] = "            {$objectVar}->{$field['name']},";
        }
        return implode("\n", $lines);
    }

    protected function modelFillable(array $fields): string
    {
        return implode("\n", array_map(fn ($f) => "        '{$f['name']}',", $fields));
    }

    protected function modelCreateArray(array $fields, string $sourceVar): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $lines[] = "            '{$field['name']}' => {$sourceVar}->{$field['name']},";
        }
        return implode("\n", $lines);
    }

    protected function modelAssignLines(array $fields, string $rowVar, string $entityVar): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $lines[] = "        {$rowVar}->{$field['name']} = {$entityVar}->{$field['name']};";
        }
        return implode("\n", $lines);
    }

    protected function objectCtorArgsFromRow(array $fields, string $rowVar): string
    {
        $lines = ["            {$rowVar}->id,"];
        foreach ($fields as $field) {
            $cast = match ($field['type']) {
                'int', 'integer' => '(int) ',
                'decimal', 'float' => '(float) ',
                'bool', 'boolean' => '(bool) ',
                default => '',
            };

            if ($field['nullable']) {
                $lines[] = "            {$rowVar}->{$field['name']} !== null ? {$cast}{$rowVar}->{$field['name']} : null,";
            } else {
                $lines[] = "            {$cast}{$rowVar}->{$field['name']},";
            }
        }
        return implode("\n", $lines);
    }

    protected function requestRules(array $fields, bool $isUpdate = false): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $rules = [];

            if ($field['nullable']) {
                $rules[] = 'nullable';
            } else {
                $rules[] = 'required';
            }

            $rules[] = match ($field['type']) {
                'string', 'text', 'date', 'datetime' => 'string',
                'int', 'integer' => 'integer',
                'decimal', 'float' => 'numeric',
                'bool', 'boolean' => 'boolean',
                default => 'string',
            };

            if (in_array($field['type'], ['string'], true)) {
                $rules[] = 'max:190';
            }

            if (in_array($field['type'], ['int', 'integer', 'decimal', 'float'], true)) {
                $rules[] = 'min:0';
            }

            $ruleString = implode("', '", $rules);
            $lines[] = "            '{$field['name']}' => ['{$ruleString}'],";
        }

        return implode("\n", $lines);
    }

    protected function dtoInputFromRequest(array $fields): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $name = $field['name'];

            $expr = match ($field['type']) {
                'int', 'integer' => "(int) \$request->input('{$name}')",
                'decimal', 'float' => "(float) \$request->input('{$name}')",
                'bool', 'boolean' => "(bool) \$request->boolean('{$name}')",
                default => $field['nullable']
                    ? "\$request->filled('{$name}') ? (string) \$request->input('{$name}') : null"
                    : "\$request->string('{$name}')->toString()",
            };

            $lines[] = "            {$name}: {$expr},";
        }

        return implode("\n", $lines);
    }

    protected function dtoJsonArray(array $fields, string $var): string
    {
        $lines = ["            'id' => \${$var}->id,"];
        foreach ($fields as $field) {
            $lines[] = "            '{$field['name']}' => \${$var}->{$field['name']},";
        }
        return implode("\n", $lines);
    }

    protected function dtoJsonArrayForMap(array $fields, string $var): string
    {
        $lines = ["                'id' => \${$var}->id,"];
        foreach ($fields as $field) {
            $lines[] = "                '{$field['name']}' => \${$var}->{$field['name']},";
        }
        return implode("\n", $lines);
    }

    protected function fillEntityFromData(array $fields, string $entityVar = '$entity', string $dataVar = '$data'): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $lines[] = "        {$entityVar}->{$field['name']} = {$dataVar}->{$field['name']};";
        }
        return implode("\n", $lines);
    }

    protected function viewTableHeaders(array $fields): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $label = Str::headline($field['name']);
            $lines[] = "      <th>{$label}</th>";
        }
        return implode("\n", $lines);
    }

    protected function viewTableCells(array $fields): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $lines[] = "        <td>{{ \$item->{$name} }}</td>";
        }
        return implode("\n", $lines);
    }

    protected function viewCreateInputs(array $fields): string
    {
        $chunks = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $label = Str::headline($name);
            $input = in_array($field['type'], ['text'], true)
                ? "    <textarea name=\"{$name}\">{{ old('{$name}') }}</textarea>"
                : "    <input name=\"{$name}\" value=\"{{ old('{$name}') }}\" />";

            $chunks[] = <<<BLADE
  <p>
    <label>{$label}</label><br/>
{$input}
    @error('{$name}') <div style="color:red">{{ \$message }}</div> @enderror
  </p>
BLADE;
        }

        return implode("\n\n", $chunks);
    }

    protected function viewEditInputs(array $fields, string $var): string
    {
        $chunks = [];
        foreach ($fields as $field) {
            $name = $field['name'];
            $label = Str::headline($name);
            $input = in_array($field['type'], ['text'], true)
                ? "    <textarea name=\"{$name}\">{{ old('{$name}', \${$var}->{$name}) }}</textarea>"
                : "    <input name=\"{$name}\" value=\"{{ old('{$name}', \${$var}->{$name}) }}\" />";

            $chunks[] = <<<BLADE
  <p>
    <label>{$label}</label><br/>
{$input}
    @error('{$name}') <div style="color:red">{{ \$message }}</div> @enderror
  </p>
BLADE;
        }

        return implode("\n\n", $chunks);
    }

    protected function viewShowFields(array $fields, string $var): string
    {
        $lines = ["<p>ID: {{ \${$var}->id }}</p>"];
        foreach ($fields as $field) {
            $label = Str::headline($field['name']);
            $lines[] = "<p>{$label}: {{ \${$var}->{$field['name']} }}</p>";
        }
        return implode("\n", $lines);
    }

    protected function migrationColumns(array $fields): string
    {
        $lines = [];
        foreach ($fields as $field) {
            $nullable = $field['nullable'] ? '->nullable()' : '';

            $line = match ($field['type']) {
                'string' => "\$table->string('{$field['name']}'){$nullable};",
                'text' => "\$table->text('{$field['name']}'){$nullable};",
                'int', 'integer' => "\$table->integer('{$field['name']}'){$nullable};",
                'decimal' => "\$table->decimal('{$field['name']}', 12, 2){$nullable};",
                'float' => "\$table->float('{$field['name']}'){$nullable};",
                'bool', 'boolean' => "\$table->boolean('{$field['name']}')->default(false);",
                'date' => "\$table->date('{$field['name']}'){$nullable};",
                'datetime' => "\$table->dateTime('{$field['name']}'){$nullable};",
                default => "\$table->string('{$field['name']}'){$nullable};",
            };

            $lines[] = "            {$line}";
        }

        return implode("\n", $lines);
    }

    protected function testPayload(array $fields): string
    {
        $pairs = [];
        foreach ($fields as $field) {
            $pairs[] = "            '{$field['name']}' => " . $this->sampleValuePhp($field) . ",";
        }
        return implode("\n", $pairs);
    }

    protected function updatedTestPayload(array $fields): string
    {
        $pairs = [];
        foreach ($fields as $field) {
            $pairs[] = "            '{$field['name']}' => " . $this->updatedSampleValuePhp($field) . ",";
        }
        return implode("\n", $pairs);
    }

    protected function sampleValuePhp(array $field): string
    {
        return match ($field['type']) {
            'string' => "'" . Str::headline($field['name']) . " Sample'",
            'text' => "'Sample description'",
            'int', 'integer' => '10',
            'decimal', 'float' => '150000.50',
            'bool', 'boolean' => 'true',
            'date' => "'2026-01-01'",
            'datetime' => "'2026-01-01 10:00:00'",
            default => "'sample'",
        };
    }

    protected function updatedSampleValuePhp(array $field): string
    {
        return match ($field['type']) {
            'string' => "'Updated " . Str::headline($field['name']) . "'",
            'text' => "'Updated description'",
            'int', 'integer' => '99',
            'decimal', 'float' => '250000.75',
            'bool', 'boolean' => 'false',
            'date' => "'2026-02-02'",
            'datetime' => "'2026-02-02 11:30:00'",
            default => "'updated'",
        };
    }

    protected function dbAssertArray(array $fields): string
    {
        $pairs = [];
        foreach ($fields as $field) {
            $pairs[] = "            '{$field['name']}' => " . $this->sampleValuePhp($field) . ",";
        }
        return implode("\n", $pairs);
    }

    protected function stubEntity(string $module, array $fields): string
    {
        $params = $this->constructorParams($fields, true);

        return <<<PHP
<?php

namespace App\Domain\\{$module}\Entities;

class {$module}
{
    public function __construct(
{$params}
    ) {}
}

PHP;
    }

    protected function stubRepositoryContract(string $module): string
    {
        $var = Str::camel($module);

        return <<<PHP
<?php

namespace App\Domain\\{$module}\Contracts;

use App\Domain\\{$module}\Entities\\{$module};

interface {$module}Repository
{
    public function create({$module} \${$var}): {$module};
    public function update({$module} \${$var}): {$module};
    public function delete(int \$id): void;
    public function findById(int \$id): ?{$module};
}

PHP;
    }

    protected function stubDto(string $module, array $fields): string
    {
        $params = $this->dtoConstructorParams($fields, true);

        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class {$module}DTO
{
    public function __construct(
{$params}
    ) {}
}

PHP;
    }

    protected function stubPagedDto(string $module, string $modulePlural): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class Paged{$modulePlural}DTO
{
    /**
     * @param {$module}DTO[] \$data
     */
    public function __construct(
        public readonly array \$data,
        public readonly array \$meta,
    ) {}
}

PHP;
    }

    protected function stubCreateDto(string $module, array $fields): string
    {
        $params = $this->dtoConstructorParams($fields, false);

        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class Create{$module}DTO
{
    public function __construct(
{$params}
    ) {}
}

PHP;
    }

    protected function stubUpdateDto(string $module, array $fields): string
    {
        $params = $this->dtoConstructorParams($fields, true);

        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class Update{$module}DTO
{
    public function __construct(
{$params}
    ) {}
}

PHP;
    }

    protected function stubCreateCommand(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Create{$module};

use App\Application\\{$module}\DTOs\Create{$module}DTO;

class Create{$module}Command
{
    public function __construct(public readonly Create{$module}DTO \$data) {}
}

PHP;
    }

    protected function stubCreateCommandHandler(string $module, array $fields): string
    {
        $var = Str::camel($module);
        $entityArgs = $this->entityArgsFromData($fields);
        $dtoArgs = $this->dtoArgsFromObject($fields, '$created');

        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Create{$module};

use App\Application\\{$module}\DTOs\\{$module}DTO;
use App\Domain\\{$module}\Contracts\\{$module}Repository;
use App\Domain\\{$module}\Entities\\{$module};

class Create{$module}CommandHandler
{
    public function __construct(private {$module}Repository \$repository) {}

    public function handle(Create{$module}Command \$command): {$module}DTO
    {
        \$data = \$command->data;

        \${$var} = new {$module}(
            id: null,
{$entityArgs}
        );

        \$created = \$this->repository->create(\${$var});

        return new {$module}DTO(
{$dtoArgs}
        );
    }
}

PHP;
    }

    protected function stubUpdateCommand(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Update{$module};

use App\Application\\{$module}\DTOs\Update{$module}DTO;

class Update{$module}Command
{
    public function __construct(public readonly Update{$module}DTO \$data) {}
}

PHP;
    }

    protected function stubUpdateCommandHandler(string $module, array $fields): string
    {
        $var = Str::camel($module);
        $assignments = $this->fillEntityFromData($fields, "\${$var}", '$data');
        $dtoArgs = $this->dtoArgsFromObject($fields, '$updated');

        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Update{$module};

use App\Application\\{$module}\DTOs\\{$module}DTO;
use App\Domain\\{$module}\Contracts\\{$module}Repository;

class Update{$module}CommandHandler
{
    public function __construct(private {$module}Repository \$repository) {}

    public function handle(Update{$module}Command \$command): {$module}DTO
    {
        \$data = \$command->data;

        \${$var} = \$this->repository->findById(\$data->id);

        if (!\${$var}) {
            throw new \DomainException('{$module} not found');
        }

{$assignments}

        \$updated = \$this->repository->update(\${$var});

        return new {$module}DTO(
{$dtoArgs}
        );
    }
}

PHP;
    }

    protected function stubDeleteCommand(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Delete{$module};

class Delete{$module}Command
{
    public function __construct(public readonly int \$id) {}
}

PHP;
    }

    protected function stubDeleteCommandHandler(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Commands\Delete{$module};

use App\Domain\\{$module}\Contracts\\{$module}Repository;

class Delete{$module}CommandHandler
{
    public function __construct(private {$module}Repository \$repository) {}

    public function handle(Delete{$module}Command \$command): void
    {
        \$this->repository->delete(\$command->id);
    }
}

PHP;
    }

    protected function stubGetByIdQuery(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Queries\Get{$module}ById;

class Get{$module}ByIdQuery
{
    public function __construct(public readonly int \$id) {}
}

PHP;
    }

    protected function stubGetByIdQueryHandler(string $module, array $fields): string
    {
        $var = Str::camel($module);
        $dtoArgs = $this->dtoArgsFromObject($fields, "\${$var}");

        return <<<PHP
<?php

namespace App\Application\\{$module}\Queries\Get{$module}ById;

use App\Application\\{$module}\DTOs\\{$module}DTO;
use App\Domain\\{$module}\Contracts\\{$module}Repository;

class Get{$module}ByIdQueryHandler
{
    public function __construct(private {$module}Repository \$repository) {}

    public function handle(Get{$module}ByIdQuery \$query): {$module}DTO
    {
        \${$var} = \$this->repository->findById(\$query->id);

        if (!\${$var}) {
            throw new \DomainException('{$module} not found');
        }

        return new {$module}DTO(
{$dtoArgs}
        );
    }
}

PHP;
    }

    protected function stubListQuery(string $module, string $modulePlural): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\Queries\List{$modulePlural};

class List{$modulePlural}Query
{
    public function __construct(
        public readonly int \$page = 1,
        public readonly int \$perPage = 15,
        public readonly ?string \$search = null,
        public readonly string \$sortBy = 'id',
        public readonly string \$sortDir = 'desc',
    ) {}
}

PHP;
    }

    protected function stubListQueryHandler(string $module, string $modulePlural, array $fields): string
    {
        $searchable = array_filter($fields, fn ($f) => in_array($f['type'], ['string', 'text'], true));
        if (empty($searchable)) {
            $searchWhere = "            \$qb->whereRaw('1 = 0');";
        } else {
            $searchLines = [];
            foreach ($searchable as $index => $field) {
                $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                $searchLines[] = "                \$w->{$method}('LOWER({$field['name']}) LIKE ?', [\"%{\$s}%\"]);";
            }
            $searchWhere = "            \$qb->where(function (\$w) use (\$s) {\n" . implode("\n", $searchLines) . "\n            });";
        }

        $allowedSort = array_merge(['id', 'created_at'], array_map(fn ($f) => "'{$f['name']}'", $fields));
        $allowedSortString = implode(', ', $allowedSort);

        $mapLines = [];
        $mapLines[] = "                \$row->id,";
        foreach ($fields as $field) {
            $cast = match ($field['type']) {
                'int', 'integer' => '(int) ',
                'decimal', 'float' => '(float) ',
                'bool', 'boolean' => '(bool) ',
                default => '',
            };

            if ($field['nullable']) {
                $mapLines[] = "                \$row->{$field['name']} !== null ? {$cast}\$row->{$field['name']} : null,";
            } else {
                $mapLines[] = "                {$cast}\$row->{$field['name']},";
            }
        }
        $mapBlock = implode("\n", $mapLines);

        return <<<PHP
<?php

namespace App\Application\\{$module}\Queries\List{$modulePlural};

use App\Application\\{$module}\DTOs\\{$module}DTO;
use App\Application\\{$module}\DTOs\Paged{$modulePlural}DTO;
use App\Infrastructure\Persistence\Eloquent\Models\\{$module}Model;
use App\Support\Helpers\Pagination;

class List{$modulePlural}QueryHandler
{
    public function handle(List{$modulePlural}Query \$query): Paged{$modulePlural}DTO
    {
        \$qb = {$module}Model::query();

        if (\$query->search) {
            \$s = mb_strtolower(trim(\$query->search));
{$searchWhere}
        }

        \$allowedSort = [{$allowedSortString}];
        \$sortBy = in_array(\$query->sortBy, \$allowedSort, true) ? \$query->sortBy : 'id';
        \$sortDir = strtolower(\$query->sortDir) === 'asc' ? 'asc' : 'desc';

        \$paginator = \$qb->orderBy(\$sortBy, \$sortDir)->paginate(
            perPage: \$query->perPage,
            page: \$query->page
        );

        \$data = [];
        foreach (\$paginator->items() as \$row) {
            \$data[] = new {$module}DTO(
{$mapBlock}
            );
        }

        return new Paged{$modulePlural}DTO(
            data: \$data,
            meta: Pagination::meta(\$paginator)
        );
    }
}

PHP;
    }

    protected function stubModel(string $module, string $table, array $fields): string
    {
        $fillable = $this->modelFillable($fields);

        return <<<PHP
<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class {$module}Model extends Model
{
    protected \$table = '{$table}';

    protected \$fillable = [
{$fillable}
    ];
}

PHP;
    }

    protected function stubRepositoryImpl(string $module, array $fields): string
    {
        $createArray = $this->modelCreateArray($fields, '$entity');
        $assignLines = $this->modelAssignLines($fields, '$row', '$entity');
        $ctorArgs = $this->objectCtorArgsFromRow($fields, '$row');

        return <<<PHP
<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\\{$module}\Contracts\\{$module}Repository;
use App\Domain\\{$module}\Entities\\{$module};
use App\Infrastructure\Persistence\Eloquent\Models\\{$module}Model;

class Eloquent{$module}Repository implements {$module}Repository
{
    public function create({$module} \$entity): {$module}
    {
        \$row = {$module}Model::create([
{$createArray}
        ]);

        return new {$module}(
{$ctorArgs}
        );
    }

    public function update({$module} \$entity): {$module}
    {
        \$row = {$module}Model::findOrFail(\$entity->id);
{$assignLines}
        \$row->save();

        return new {$module}(
{$ctorArgs}
        );
    }

    public function delete(int \$id): void
    {
        {$module}Model::query()->where('id', \$id)->delete();
    }

    public function findById(int \$id): ?{$module}
    {
        \$row = {$module}Model::find(\$id);

        if (!\$row) {
            return null;
        }

        return new {$module}(
{$ctorArgs}
        );
    }
}

PHP;
    }

    protected function stubStoreRequest(string $module, array $fields): string
    {
        $rules = $this->requestRules($fields, false);

        return <<<PHP
<?php

namespace App\Presentation\Http\Requests\\{$module};

use Illuminate\Foundation\Http\FormRequest;

class Store{$module}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rules}
        ];
    }
}

PHP;
    }

    protected function stubUpdateRequest(string $module, array $fields): string
    {
        $rules = $this->requestRules($fields, true);

        return <<<PHP
<?php

namespace App\Presentation\Http\Requests\\{$module};

use Illuminate\Foundation\Http\FormRequest;

class Update{$module}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
{$rules}
        ];
    }
}

PHP;
    }

    protected function stubApiController(string $module, string $modulePlural, array $fields): string
    {
        $moduleVar = Str::camel($module);
        $dtoInput = $this->dtoInputFromRequest($fields);
        $jsonSingle = $this->dtoJsonArray($fields, $moduleVar);
        $jsonMap = $this->dtoJsonArrayForMap($fields, 'dto');

        return <<<PHP
<?php

namespace App\Presentation\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;
use App\Application\\{$module}\DTOs\Create{$module}DTO;
use App\Application\\{$module}\DTOs\Update{$module}DTO;
use App\Application\\{$module}\Commands\Create{$module}\Create{$module}Command;
use App\Application\\{$module}\Commands\Update{$module}\Update{$module}Command;
use App\Application\\{$module}\Commands\Delete{$module}\Delete{$module}Command;
use App\Application\\{$module}\Queries\Get{$module}ById\Get{$module}ByIdQuery;
use App\Application\\{$module}\Queries\List{$modulePlural}\List{$modulePlural}Query;
use App\Presentation\Http\Requests\\{$module}\Store{$module}Request;
use App\Presentation\Http\Requests\\{$module}\Update{$module}Request;

class {$module}ApiController
{
    public function index(Request \$request, QueryBus \$queryBus)
    {
        \$page = max(1, (int) \$request->query('page', 1));
        \$perPage = min(max((int) \$request->query('per_page', 15), 1), 100);

        \$result = \$queryBus->ask(new List{$modulePlural}Query(
            page: \$page,
            perPage: \$perPage,
            search: is_string(\$request->query('search')) ? \$request->query('search') : null,
            sortBy: is_string(\$request->query('sort_by')) ? \$request->query('sort_by') : 'id',
            sortDir: is_string(\$request->query('sort_dir')) ? \$request->query('sort_dir') : 'desc',
        ));

        return response()->json([
            'data' => array_map(fn(\$dto) => [
{$jsonMap}
            ], \$result->data),
            'meta' => \$result->meta,
        ]);
    }

    public function store(Store{$module}Request \$request, CommandBus \$commandBus)
    {
        \$dto = new Create{$module}DTO(
{$dtoInput}
        );

        \${$moduleVar} = \$commandBus->dispatch(new Create{$module}Command(\$dto));

        return response()->json([
{$jsonSingle}
        ], 201);
    }

    public function show(int \$id, QueryBus \$queryBus)
    {
        \${$moduleVar} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

        return response()->json([
{$jsonSingle}
        ]);
    }

    public function update(int \$id, Update{$module}Request \$request, CommandBus \$commandBus)
    {
        \$dto = new Update{$module}DTO(
            id: \$id,
{$dtoInput}
        );

        \${$moduleVar} = \$commandBus->dispatch(new Update{$module}Command(\$dto));

        return response()->json([
{$jsonSingle}
        ]);
    }

    public function destroy(int \$id, CommandBus \$commandBus)
    {
        \$commandBus->dispatch(new Delete{$module}Command(\$id));

        return response()->json(['message' => '{$module} deleted']);
    }
}

PHP;
    }

    protected function stubWebController(string $module, string $modulePlural, string $modulePluralSnake, array $fields): string
    {
        $moduleVar = Str::camel($module);
        $dtoInput = $this->dtoInputFromRequest($fields);

        return <<<PHP
<?php

namespace App\Presentation\Http\Controllers\Web;

use DomainException;
use Illuminate\Http\Request;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;
use App\Support\Helpers\PaginationLinks;
use App\Application\\{$module}\DTOs\Create{$module}DTO;
use App\Application\\{$module}\DTOs\Update{$module}DTO;
use App\Application\\{$module}\Commands\Create{$module}\Create{$module}Command;
use App\Application\\{$module}\Commands\Update{$module}\Update{$module}Command;
use App\Application\\{$module}\Commands\Delete{$module}\Delete{$module}Command;
use App\Application\\{$module}\Queries\Get{$module}ById\Get{$module}ByIdQuery;
use App\Application\\{$module}\Queries\List{$modulePlural}\List{$modulePlural}Query;
use App\Presentation\Http\Requests\\{$module}\Store{$module}Request;
use App\Presentation\Http\Requests\\{$module}\Update{$module}Request;

class {$module}WebController
{
    public function index(Request \$request, QueryBus \$queryBus)
    {
        \$page = max(1, (int) \$request->query('page', 1));
        \$perPage = min(max((int) \$request->query('per_page', 10), 1), 50);
        \$sortBy = is_string(\$request->query('sort_by')) ? \$request->query('sort_by') : 'id';
        \$sortDir = is_string(\$request->query('sort_dir')) ? \$request->query('sort_dir') : 'desc';

        \$result = \$queryBus->ask(new List{$modulePlural}Query(
            page: \$page,
            perPage: \$perPage,
            search: is_string(\$request->query('search')) ? \$request->query('search') : null,
            sortBy: \$sortBy,
            sortDir: \$sortDir,
        ));

        \$paginationLinks = PaginationLinks::build(
            basePath: '/{$modulePluralSnake}',
            query: [
                'search' => is_string(\$request->query('search')) ? \$request->query('search') : null,
                'per_page' => \$perPage,
                'sort_by' => \$sortBy,
                'sort_dir' => \$sortDir,
            ],
            currentPage: \$result->meta['current_page'],
            lastPage: \$result->meta['last_page'],
        );

        return view('{$modulePluralSnake}.index', [
            '{$modulePluralSnake}' => \$result->data,
            'meta' => \$result->meta,
            'paginationLinks' => \$paginationLinks,
            'filters' => [
                'search' => (string) \$request->query('search', ''),
                'per_page' => \$perPage,
                'sort_by' => \$sortBy,
                'sort_dir' => \$sortDir,
            ],
        ]);
    }

    public function create()
    {
        return view('{$modulePluralSnake}.create');
    }

    public function store(Store{$module}Request \$request, CommandBus \$commandBus)
    {
        try {
            \$dto = new Create{$module}DTO(
{$dtoInput}
            );

            \$commandBus->dispatch(new Create{$module}Command(\$dto));

            return redirect('/{$modulePluralSnake}')->with('success', '{$module} created');
        } catch (DomainException \$e) {
            return back()->withInput()->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function show(int \$id, QueryBus \$queryBus)
    {
        try {
            \${$moduleVar} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

            return view('{$modulePluralSnake}.show', ['{$moduleVar}' => \${$moduleVar}]);
        } catch (DomainException \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function edit(int \$id, QueryBus \$queryBus)
    {
        try {
            \${$moduleVar} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

            return view('{$modulePluralSnake}.edit', ['{$moduleVar}' => \${$moduleVar}]);
        } catch (DomainException \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function update(int \$id, Update{$module}Request \$request, CommandBus \$commandBus)
    {
        try {
            \$dto = new Update{$module}DTO(
                id: \$id,
{$dtoInput}
            );

            \$commandBus->dispatch(new Update{$module}Command(\$dto));

            return redirect('/{$modulePluralSnake}/'.\$id)->with('success', '{$module} updated');
        } catch (DomainException \$e) {
            return back()->withInput()->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function destroy(int \$id, CommandBus \$commandBus)
    {
        try {
            \$commandBus->dispatch(new Delete{$module}Command(\$id));

            return redirect('/{$modulePluralSnake}')->with('success', '{$module} deleted');
        } catch (DomainException \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', 'Terjadi kesalahan pada server');
        }
    }
}

PHP;
    }

    protected function stubIndexView(string $module, string $modulePlural, string $modulePluralSnake, array $fields): string
    {
        $headers = $this->viewTableHeaders($fields);
        $cells = $this->viewTableCells($fields);

        return <<<BLADE
<h1>{$modulePlural}</h1>

@include('partials.flash')

@php
    \$baseQuery = [
        'search' => \$filters['search'] ?? '',
        'per_page' => \$filters['per_page'] ?? 20,
    ];

    \$currentSortBy = \$filters['sort_by'] ?? 'id';
    \$currentSortDir = \$filters['sort_dir'] ?? 'desc';

    \$sortUrl = function(string \$column) use (\$baseQuery, \$currentSortBy, \$currentSortDir) {
        \$dir = 'asc';
        if (\$currentSortBy === \$column) {
            \$dir = \$currentSortDir === 'asc' ? 'desc' : 'asc';
        }
        \$q = array_merge(\$baseQuery, ['sort_by' => \$column, 'sort_dir' => \$dir, 'page' => 1]);
        return '/{$modulePluralSnake}?' . http_build_query(\$q);
    };

    \$sortIcon = function(string \$column) use (\$currentSortBy, \$currentSortDir) {
        if (\$currentSortBy !== \$column) return '';
        return \$currentSortDir === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

@php
  \$offset = ((\$meta['current_page'] ?? 1) - 1) * (\$meta['per_page'] ?? 20);
@endphp

<form method="GET" action="/{$modulePluralSnake}" style="margin-bottom: 12px">
  <input name="search" value="{{ \$filters['search'] }}" placeholder="Search" />
  <button type="submit">Search</button>
  <a href="/{$modulePluralSnake}/create">Create</a>
</form>

<table border="1" cellpadding="8" cellspacing="0">
  <thead>
    <tr>
      <th>No</th>
      <th><a href="{{ \$sortUrl('id') }}">ID{{ \$sortIcon('id') }}</a></th>
{$headers}
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    @foreach(\${$modulePluralSnake} as \$item)
      <tr>
        <td>{{ \$offset + \$loop->iteration }}</td>
        <td>{{ \$item->id }}</td>
{$cells}
        <td>
          <a href="/{$modulePluralSnake}/{{ \$item->id }}">Show</a>
          <a href="/{$modulePluralSnake}/{{ \$item->id }}/edit">Edit</a>
          <form method="POST" action="/{$modulePluralSnake}/{{ \$item->id }}" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" onclick="return confirm('Delete?')">Delete</button>
          </form>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

@if((\$meta['last_page'] ?? 1) > 1)
    <div style="margin-top: 12px; display:flex; gap:6px; align-items:center; flex-wrap:wrap;">
        @foreach(\$paginationLinks as \$item)
            @if(\$item['label'] === '...')
                <span style="padding:6px 10px;">...</span>
            @elseif(\$item['disabled'])
                <span style="padding:6px 10px; color:#999; border:1px solid #ddd;">{{ \$item['label'] }}</span>
            @elseif(\$item['active'])
                <span style="padding:6px 10px; font-weight:bold; border:1px solid #000;">{{ \$item['label'] }}</span>
            @else
                <a href="{{ \$item['url'] }}" style="padding:6px 10px; border:1px solid #ddd; text-decoration:none;">
                    {{ \$item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
@endif

BLADE;
    }

    protected function stubCreateView(string $module, string $modulePluralSnake, array $fields): string
    {
        $inputs = $this->viewCreateInputs($fields);

        return <<<BLADE
<h1>Create {$module}</h1>

@include('partials.flash')

<form method="POST" action="/{$modulePluralSnake}">
  @csrf

{$inputs}

  <button type="submit">Save</button>
  <a href="/{$modulePluralSnake}">Back</a>
</form>

BLADE;
    }

    protected function stubShowView(string $module, string $moduleVar, array $fields): string
    {
        $pluralSnake = Str::snake(Str::pluralStudly($module));
        $showFields = $this->viewShowFields($fields, $moduleVar);

        return <<<BLADE
<h1>{$module} Detail</h1>

{$showFields}

<p>
  <a href="/{$pluralSnake}/{{ \${$moduleVar}->id }}/edit">Edit</a> |
  <a href="/{$pluralSnake}">Back</a>
</p>

BLADE;
    }

    protected function stubEditView(string $module, string $moduleVar, string $modulePluralSnake, array $fields): string
    {
        $inputs = $this->viewEditInputs($fields, $moduleVar);

        return <<<BLADE
<h1>Edit {$module}</h1>

@include('partials.flash')

<form method="POST" action="/{$modulePluralSnake}/{{ \${$moduleVar}->id }}">
  @csrf
  @method('PUT')

{$inputs}

  <button type="submit">Update</button>
  <a href="/{$modulePluralSnake}/{{ \${$moduleVar}->id }}">Cancel</a>
</form>

BLADE;
    }

    protected function stubRoutes(string $module, string $modulePluralSnake): string
    {
        return <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Presentation\Http\Controllers\Api\\{$module}ApiController;
use App\Presentation\Http\Controllers\Web\\{$module}WebController;

// API
Route::get('/{$modulePluralSnake}', [{$module}ApiController::class, 'index']);
Route::post('/{$modulePluralSnake}', [{$module}ApiController::class, 'store']);
Route::get('/{$modulePluralSnake}/{id}', [{$module}ApiController::class, 'show']);
Route::put('/{$modulePluralSnake}/{id}', [{$module}ApiController::class, 'update']);
Route::delete('/{$modulePluralSnake}/{id}', [{$module}ApiController::class, 'destroy']);

// WEB
Route::get('/{$modulePluralSnake}', [{$module}WebController::class, 'index']);
Route::get('/{$modulePluralSnake}/create', [{$module}WebController::class, 'create']);
Route::post('/{$modulePluralSnake}', [{$module}WebController::class, 'store']);
Route::get('/{$modulePluralSnake}/{id}', [{$module}WebController::class, 'show']);
Route::get('/{$modulePluralSnake}/{id}/edit', [{$module}WebController::class, 'edit']);
Route::put('/{$modulePluralSnake}/{id}', [{$module}WebController::class, 'update']);
Route::delete('/{$modulePluralSnake}/{id}', [{$module}WebController::class, 'destroy']);

PHP;
    }

    protected function stubMigration(string $table, array $fields): string
    {
        $columns = $this->migrationColumns($fields);

        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
{$columns}
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};

PHP;
    }

    protected function stubFeatureApiTest(string $module, string $table, array $fields): string
    {
        $payload = $this->testPayload($fields);
        $updatedPayload = $this->updatedTestPayload($fields);

        $jsonKeys = ["'id'"];
        foreach ($fields as $field) {
            $jsonKeys[] = "'{$field['name']}'";
        }
        $jsonStructure = implode(',', $jsonKeys);

        return <<<PHP
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class {$module}ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_{$table}_via_api(): void
    {
        \$res = \$this->postJson('/api/{$table}', [
{$payload}
        ]);

        \$res->assertStatus(201)
            ->assertJsonStructure([{$jsonStructure}]);
    }

    public function test_can_list_{$table}_via_api(): void
    {
        \$this->postJson('/api/{$table}', [
{$payload}
        ]);

        \$res = \$this->getJson('/api/{$table}');

        \$res->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page','per_page','total','last_page'],
            ]);
    }

    public function test_can_update_{$table}_via_api(): void
    {
        \$create = \$this->postJson('/api/{$table}', [
{$payload}
        ])->json();

        \$res = \$this->putJson('/api/{$table}/'.\$create['id'], [
{$updatedPayload}
        ]);

        \$res->assertStatus(200);
    }
}

PHP;
    }

    protected function stubFeatureWebTest(string $module, string $modulePlural, string $table, array $fields): string
    {
        $payload = $this->testPayload($fields);
        $firstStringField = collect($fields)->first(fn ($f) => in_array($f['type'], ['string', 'text'], true));
        $seeValue = $firstStringField ? trim($this->sampleValuePhp($firstStringField), "'") : $modulePlural;

        return <<<PHP
<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\\{$module}Model;

class {$module}WebTest extends TestCase
{
    use RefreshDatabase;

    public function test_{$table}_page_loads(): void
    {
        {$module}Model::create([
{$payload}
        ]);

        \$res = \$this->get('/{$table}');

        \$res->assertStatus(200);
        \$res->assertSee('{$modulePlural}');
        \$res->assertSee('{$seeValue}');
    }

    public function test_create_{$module}_form_loads(): void
    {
        \$res = \$this->get('/{$table}/create');
        \$res->assertStatus(200)->assertSee('Create {$module}');
    }
}

PHP;
    }

    protected function stubUnitCreateHandlerTest(string $module, array $fields): string
    {
        $ctorPayload = [];
        $resultCtor = ["                id: 1,"];
        $assertions = ["        \$this->assertSame(1, \$result->id);"];

        foreach ($fields as $field) {
            $value = $this->sampleValuePhp($field);
            $ctorPayload[] = $value;
            $resultCtor[] = "                {$field['name']}: {$value},";
            if ($field['type'] === 'string') {
                $assertions[] = "        \$this->assertSame({$value}, \$result->{$field['name']});";
                break;
            }
        }

        $dtoCtor = implode(', ', $ctorPayload);
        $resultCtorBlock = implode("\n", $resultCtor);
        $assertionBlock = implode("\n", $assertions);

        return <<<PHP
<?php

namespace Tests\Unit\\{$module};

use Tests\TestCase;
use Mockery;
use App\Domain\\{$module}\Contracts\\{$module}Repository;
use App\Application\\{$module}\DTOs\Create{$module}DTO;
use App\Application\\{$module}\Commands\Create{$module}\Create{$module}Command;
use App\Application\\{$module}\Commands\Create{$module}\Create{$module}CommandHandler;
use App\Domain\\{$module}\Entities\\{$module};

class Create{$module}CommandHandlerTest extends TestCase
{
    public function test_create_{$this->snake($module)}_handler_returns_dto(): void
    {
        \$repo = Mockery::mock({$module}Repository::class);

        \$repo->shouldReceive('create')
            ->once()
            ->andReturn(new {$module}(
{$resultCtorBlock}
            ));

        \$handler = new Create{$module}CommandHandler(\$repo);

        \$dto = new Create{$module}DTO({$dtoCtor});
        \$result = \$handler->handle(new Create{$module}Command(\$dto));

{$assertionBlock}
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

PHP;
    }

    protected function snake(string $value): string
    {
        return Str::snake($value);
    }
}