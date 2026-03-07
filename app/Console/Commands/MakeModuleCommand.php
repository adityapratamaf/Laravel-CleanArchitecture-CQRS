<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : Module name, e.g. Product}';
    protected $description = 'Generate a Clean Architecture + CQRS module';

    public function handle(): int
    {
        $module = Str::studly($this->argument('name'));
        $moduleVar = Str::camel($module);
        $modulePlural = Str::pluralStudly($module);
        $modulePluralVar = Str::camel($modulePlural);
        $moduleSnake = Str::snake($module);
        $modulePluralSnake = Str::snake($modulePlural);

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

        foreach ($basePaths as $path) {
            File::ensureDirectoryExists($path);
        }

        $files = [
            app_path("Domain/{$module}/Entities/{$module}.php")
                => $this->stubEntity($module),

            app_path("Domain/{$module}/Contracts/{$module}Repository.php")
                => $this->stubRepositoryContract($module),

            app_path("Application/{$module}/DTOs/{$module}DTO.php")
                => $this->stubDto($module),

            app_path("Application/{$module}/DTOs/Paged{$modulePlural}DTO.php")
                => $this->stubPagedDto($module, $modulePlural),

            app_path("Application/{$module}/DTOs/Create{$module}DTO.php")
                => $this->stubCreateDto($module),

            app_path("Application/{$module}/DTOs/Update{$module}DTO.php")
                => $this->stubUpdateDto($module),

            app_path("Application/{$module}/Commands/Create{$module}/Create{$module}Command.php")
                => $this->stubCreateCommand($module),

            app_path("Application/{$module}/Commands/Create{$module}/Create{$module}CommandHandler.php")
                => $this->stubCreateCommandHandler($module),

            app_path("Application/{$module}/Commands/Update{$module}/Update{$module}Command.php")
                => $this->stubUpdateCommand($module),

            app_path("Application/{$module}/Commands/Update{$module}/Update{$module}CommandHandler.php")
                => $this->stubUpdateCommandHandler($module),

            app_path("Application/{$module}/Commands/Delete{$module}/Delete{$module}Command.php")
                => $this->stubDeleteCommand($module),

            app_path("Application/{$module}/Commands/Delete{$module}/Delete{$module}CommandHandler.php")
                => $this->stubDeleteCommandHandler($module),

            app_path("Application/{$module}/Queries/Get{$module}ById/Get{$module}ByIdQuery.php")
                => $this->stubGetByIdQuery($module),

            app_path("Application/{$module}/Queries/Get{$module}ById/Get{$module}ByIdQueryHandler.php")
                => $this->stubGetByIdQueryHandler($module),

            app_path("Application/{$module}/Queries/List{$modulePlural}/List{$modulePlural}Query.php")
                => $this->stubListQuery($module, $modulePlural),

            app_path("Application/{$module}/Queries/List{$modulePlural}/List{$modulePlural}QueryHandler.php")
                => $this->stubListQueryHandler($module, $modulePlural),

            app_path("Infrastructure/Persistence/Eloquent/Models/{$module}Model.php")
                => $this->stubModel($module, $modulePluralSnake),

            app_path("Infrastructure/Persistence/Eloquent/Repositories/Eloquent{$module}Repository.php")
                => $this->stubRepositoryImpl($module, $modulePluralSnake),

            app_path("Presentation/Http/Requests/{$module}/Store{$module}Request.php")
                => $this->stubStoreRequest($module),

            app_path("Presentation/Http/Requests/{$module}/Update{$module}Request.php")
                => $this->stubUpdateRequest($module),

            app_path("Presentation/Http/Controllers/Api/{$module}ApiController.php")
                => $this->stubApiController($module, $modulePlural, $modulePluralSnake),

            app_path("Presentation/Http/Controllers/Web/{$module}WebController.php")
                => $this->stubWebController($module, $modulePlural, $modulePluralSnake),

            app_path("Presentation/Views/{$modulePluralSnake}/index.blade.php")
                => $this->stubIndexView($module, $modulePlural, $modulePluralSnake),

            app_path("Presentation/Views/{$modulePluralSnake}/create.blade.php")
                => $this->stubCreateView($module, $modulePluralSnake),

            app_path("Presentation/Views/{$modulePluralSnake}/show.blade.php")
                => $this->stubShowView($module, $moduleVar),

            app_path("Presentation/Views/{$modulePluralSnake}/edit.blade.php")
                => $this->stubEditView($module, $moduleVar, $modulePluralSnake),

            app_path("Presentation/Routes/{$modulePluralSnake}.php")
                => $this->stubRoutes($module, $modulePluralSnake),
        ];

        foreach ($files as $path => $content) {
            if (File::exists($path)) {
                $this->warn("Skip, already exists: {$path}");
                continue;
            }

            File::put($path, $content);
            $this->info("Created: {$path}");
        }

        $this->newLine();
        $this->info("Module {$module} generated successfully.");
        $this->warn("Next steps:");
        $this->line("1. Tambahkan binding repository di file CQRSServiceProvider.php");
        $this->line("2. Tambahkan routes dari app/Presentation/Routes/{$modulePluralSnake}.php");
        $this->line("3. Buat migration & seeder untuk tabel {$modulePluralSnake}");
        $this->line("4. Sesuaikan fields DTO/Entity/Request/View sesuai kebutuhan module");

        return self::SUCCESS;
    }

    protected function stubEntity(string $module): string
    {
        return <<<PHP
<?php

namespace App\Domain\\{$module}\Entities;

class {$module}
{
    public function __construct(
        public readonly ?int \$id,
        public string \$name,
    ) {}
}

PHP;
    }

    protected function stubRepositoryContract(string $module): string
    {
        return <<<PHP
<?php

namespace App\Domain\\{$module}\Contracts;

use App\Domain\\{$module}\Entities\\{$module};

interface {$module}Repository
{
    public function create({$module} \${$this->camel($module)}): {$module};
    public function update({$module} \${$this->camel($module)}): {$module};
    public function delete(int \$id): void;
    public function findById(int \$id): ?{$module};
}

PHP;
    }

    protected function stubDto(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class {$module}DTO
{
    public function __construct(
        public readonly int \$id,
        public readonly string \$name,
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

    protected function stubCreateDto(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class Create{$module}DTO
{
    public function __construct(
        public readonly string \$name,
    ) {}
}

PHP;
    }

    protected function stubUpdateDto(string $module): string
    {
        return <<<PHP
<?php

namespace App\Application\\{$module}\DTOs;

class Update{$module}DTO
{
    public function __construct(
        public readonly int \$id,
        public readonly string \$name,
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

    protected function stubCreateCommandHandler(string $module): string
    {
        $var = $this->camel($module);

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
            name: \$data->name,
        );

        \$created = \$this->repository->create(\${$var});

        return new {$module}DTO(
            \$created->id,
            \$created->name,
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

    protected function stubUpdateCommandHandler(string $module): string
    {
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

        \${$this->camel($module)} = \$this->repository->findById(\$data->id);

        if (!\${$this->camel($module)}) {
            throw new \DomainException('{$module} not found');
        }

        \${$this->camel($module)}->name = \$data->name;

        \$updated = \$this->repository->update(\${$this->camel($module)});

        return new {$module}DTO(
            \$updated->id,
            \$updated->name,
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

    protected function stubGetByIdQueryHandler(string $module): string
    {
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
        \${$this->camel($module)} = \$this->repository->findById(\$query->id);

        if (!\${$this->camel($module)}) {
            throw new \DomainException('{$module} not found');
        }

        return new {$module}DTO(
            \${$this->camel($module)}->id,
            \${$this->camel($module)}->name,
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

    protected function stubListQueryHandler(string $module, string $modulePlural): string
    {
        $moduleSnakePlural = Str::snake(Str::pluralStudly($module));

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
            \$qb->where(function (\$w) use (\$s) {
                \$w->whereRaw('LOWER(name) LIKE ?', ["%{\$s}%"]);
            });
        }

        \$allowedSort = ['id', 'name', 'created_at'];
        \$sortBy = in_array(\$query->sortBy, \$allowedSort, true) ? \$query->sortBy : 'id';
        \$sortDir = strtolower(\$query->sortDir) === 'asc' ? 'asc' : 'desc';

        \$paginator = \$qb->orderBy(\$sortBy, \$sortDir)->paginate(
            perPage: \$query->perPage,
            page: \$query->page
        );

        \$data = [];
        foreach (\$paginator->items() as \$row) {
            \$data[] = new {$module}DTO(
                \$row->id,
                \$row->name,
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

    protected function stubModel(string $module, string $table): string
    {
        return <<<PHP
<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

class {$module}Model extends Model
{
    protected \$table = '{$table}';

    protected \$fillable = [
        'name',
    ];
}

PHP;
    }

    protected function stubRepositoryImpl(string $module, string $table): string
    {
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
            'name' => \$entity->name,
        ]);

        return new {$module}(
            \$row->id,
            \$row->name,
        );
    }

    public function update({$module} \$entity): {$module}
    {
        \$row = {$module}Model::findOrFail(\$entity->id);
        \$row->name = \$entity->name;
        \$row->save();

        return new {$module}(
            \$row->id,
            \$row->name,
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
            \$row->id,
            \$row->name,
        );
    }
}

PHP;
    }

    protected function stubStoreRequest(string $module): string
    {
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
            'name' => ['required', 'string', 'max:190'],
        ];
    }
}

PHP;
    }

    protected function stubUpdateRequest(string $module): string
    {
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
            'name' => ['required', 'string', 'max:190'],
        ];
    }
}

PHP;
    }

    protected function stubApiController(string $module, string $modulePlural, string $modulePluralSnake): string
    {
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

        \$start = ((\$result->meta['current_page'] - 1) * \$result->meta['per_page']) + 1;

        return response()->json([
            'data' => array_map(function(\$dto) use (&\$start) {
                return [
                    'no' => \$start++,
                    'id' => \$dto->id,
                    'name' => \$dto->name,
                ];
            }, \$result->data),
            'meta' => \$result->meta,
        ]);
    }

    public function store(Store{$module}Request \$request, CommandBus \$commandBus)
    {
        \$dto = new Create{$module}DTO(
            name: \$request->string('name')->toString(),
        );

        \${$this->camel($module)} = \$commandBus->dispatch(new Create{$module}Command(\$dto));

        return response()->json([
            'id' => \${$this->camel($module)}->id,
            'name' => \${$this->camel($module)}->name,
        ], 201);
    }

    public function show(int \$id, QueryBus \$queryBus)
    {
        \${$this->camel($module)} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

        return response()->json([
            'id' => \${$this->camel($module)}->id,
            'name' => \${$this->camel($module)}->name,
        ]);
    }

    public function update(int \$id, Update{$module}Request \$request, CommandBus \$commandBus)
    {
        \$dto = new Update{$module}DTO(
            id: \$id,
            name: \$request->string('name')->toString(),
        );

        \${$this->camel($module)} = \$commandBus->dispatch(new Update{$module}Command(\$dto));

        return response()->json([
            'id' => \${$this->camel($module)}->id,
            'name' => \${$this->camel($module)}->name,
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

    protected function stubWebController(string $module, string $modulePlural, string $modulePluralSnake): string
    {
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
            sortBy: is_string(\$request->query('sort_by')) ? \$request->query('sort_by') : 'id',
            sortDir: is_string(\$request->query('sort_dir')) ? \$request->query('sort_dir') : 'desc',
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
                name: \$request->string('name')->toString(),
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
            \${$this->camel($module)} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

            return view('{$modulePluralSnake}.show', ['{$this->camel($module)}' => \${$this->camel($module)}]);
        } catch (DomainException \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', \$e->getMessage());
        } catch (\Throwable \$e) {
            return redirect('/{$modulePluralSnake}')->with('error', 'Terjadi kesalahan pada server');
        }
    }

    public function edit(int \$id, QueryBus \$queryBus)
    {
        try {
            \${$this->camel($module)} = \$queryBus->ask(new Get{$module}ByIdQuery(\$id));

            return view('{$modulePluralSnake}.edit', ['{$this->camel($module)}' => \${$this->camel($module)}]);
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
                name: \$request->string('name')->toString(),
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

    protected function stubIndexView(string $module, string $modulePlural, string $modulePluralSnake): string
    {
        return <<<BLADE
<h1>{$modulePlural}</h1>

@include('partials.flash')

<!-- Helper -->
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
  <input name="search" value="{{ \$filters['search'] }}" placeholder="Search name" />
  <button type="submit">Search</button>
  <a href="/{$modulePluralSnake}/create">Create</a>
</form>

<table border="1" cellpadding="8" cellspacing="0">
  <thead>
    <tr>
      <th>No</th>
      <th><a href="{{ \$sortUrl('id') }}">ID{{ \$sortIcon('id') }}</a></th>
      <th><a href="{{ \$sortUrl('name') }}">Name{{ \$sortIcon('name') }}</a></th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
    @foreach(\${$modulePluralSnake} as \$item)
      <tr>
        <td>{{ \$offset + \$loop->iteration }}</td>
        <td>{{ \$item->id }}</td>
        <td><a href="/{$modulePluralSnake}/{{ \$item->id }}">{{ \$item->name }}</a></td>
        <td>
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

<p style="margin-top: 12px">
  Page {{ \$meta['current_page'] }} of {{ \$meta['last_page'] }} |
  Total {{ \$meta['total'] }}
</p>

@if(\$meta['last_page'] > 1)
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

    protected function stubCreateView(string $module, string $modulePluralSnake): string
    {
        return <<<BLADE
<h1>Create {$module}</h1>

@include('partials.flash')

<form method="POST" action="/{$modulePluralSnake}">
  @csrf

  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name') }}" />
    @error('name') <div style="color:red">{{ \$message }}</div> @enderror
  </p>

  <button type="submit">Save</button>
  <a href="/{$modulePluralSnake}">Back</a>
</form>

BLADE;
    }

    protected function stubShowView(string $module, string $moduleVar): string
    {
        $pluralSnake = Str::snake(Str::pluralStudly($module));

        return <<<BLADE
<h1>{$module} Detail</h1>

<p>ID: {{ \${$moduleVar}->id }}</p>
<p>Name: {{ \${$moduleVar}->name }}</p>

<p>
  <a href="/{$pluralSnake}/{{ \${$moduleVar}->id }}/edit">Edit</a> |
  <a href="/{$pluralSnake}">Back</a>
</p>

BLADE;
    }

    protected function stubEditView(string $module, string $moduleVar, string $modulePluralSnake): string
    {
        return <<<BLADE
<h1>Edit {$module}</h1>

@include('partials.flash')

<form method="POST" action="/{$modulePluralSnake}/{{ \${$moduleVar}->id }}">
  @csrf
  @method('PUT')

  <p>
    <label>Name</label><br/>
    <input name="name" value="{{ old('name', \${$moduleVar}->name) }}" />
    @error('name') <div style="color:red">{{ \$message }}</div> @enderror
  </p>

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

/*
|--------------------------------------------------------------------------
| {$module} Routes
|--------------------------------------------------------------------------
| Copy route ini ke app/Presentation/Routes/api.php atau web.php
| Setelah di copy dan paste di masing-masing routes (api.php atau web.php), hapus file ini agar tetap clean.
*/

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

    protected function camel(string $value): string
    {
        return Str::camel($value);
    }
}