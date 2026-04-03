start:
	php -S localhost:8000 public/index.php
migrate:
	php app/Framework/Services/DB/Eloquent/Migrations/run_migrations.php
rollback:
	php app/Framework/Services/DB/Eloquent/Migrations/rollback_all_migrations.php
test:
	php app/Framework/Services/CLI/exec.php test
ecs:
	vendor/bin/ecs check --fix
psalm-alter:
	vendor/bin/psalm --alter --issues=InvalidReturnType,InvalidNullableReturnType,InvalidFalsableReturnType,UnnecessaryVarAnnotation
psalm-baseline:
	vendor/bin/psalm --set-baseline=psalm-baseline.xml --update-baseline
rector:
	vendor/bin/rector process --config=rector.php
rector-dry-run:
	vendor/bin/rector process --dry-run --config=rector.php
