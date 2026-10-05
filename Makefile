mago:
	vendor/bin/mago lint --fix --unsafe --potentially-unsafe --dry-run
	vendor/bin/mago analyze --fix --unsafe --potentially-unsafe --dry-run

test-start:
	php -S localhost:8000 tests/public/main.php

test-cli-list:
	php tests/public/main.php list

test-cli-greet:
	php tests/public/main.php greet --text=ok

pre-commit:
	 ln -s ../../hooks/pre-commit .git/hooks/pre-commit
	 chmod +x .git/hooks/pre-commit

clear-test-logs:
	 @find ./tests/logs -name "error_*.log" -type f -mtime +7 -delete
	 @find ./tests/logs -name "php_errors_*.log" -type f -mtime +7 -delete
