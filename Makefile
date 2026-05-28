ecs:
	vendor/bin/ecs check --fix

psalm:
	vendor/bin/psalm

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
