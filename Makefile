start:
	php -S localhost:8000 public/index.php
migrate:
	php app/Framework/Services/DB/Eloquent/Migrations/run_migrations.php
rollback:
	php app/Framework/Services/DB/Eloquent/Migrations/rollback_all_migrations.php
