start:
	php -S localhost:8000 public/index.php

migrate:
	php framework/DB/Eloquent/Migrations/run_migrations.php

rollback:
	php framework/DB/Eloquent/Migrations/rollback_all_migrations.php

test:
	php framework/Services/CLI/exec.php test

ecs:
	vendor/bin/ecs check --fix

psalm:
	vendor/bin/psalm

psalm-alter:
	vendor/bin/psalm --alter --issues=all

psalm-alter-dry-run:
	vendor/bin/psalm --alter --issues=InvalidNullableReturnType --dry-run

psalm-baseline-set:
	vendor/bin/psalm --set-baseline=psalm-baseline.xml

psalm-baseline-update:
	vendor/bin/psalm --update-baseline

rector:
	vendor/bin/rector process --config=rector.php

rector-dry-run:
	vendor/bin/rector process --dry-run --config=rector.

pre-commit:
	 ln -s ../../hooks/pre-commit .git/hooks/pre-commit
	 chmod +x .git/hooks/pre-commit

clear-logs:
	 @find ./logs -name "error_*.log" -type f -mtime +7 -delete
	 @find ./logs -name "php_errors_*.log" -type f -mtime +7 -delete
