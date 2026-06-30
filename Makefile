# My little taskrunner
SHELL = cmd.exe

up:
	docker compose up -d --build

down:
	docker compose down

down-clean:
	docker compose down -v

ps:
	docker compose ps

logs-app:
	docker compose logs -f app

logs-db:
	docker compose logs -f db

logs-nginx:
	docker compose logs -f nginx

db-schema:
	type sql\schema.sql | docker compose exec -T db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\""

db-seed:
	type sql\seed.sql | docker compose exec -T db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\""

db-shell:
	docker compose exec db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\""

db-players:
	docker compose exec db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\" -e \"SELECT * FROM players;\""

db-events:
	docker compose exec db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\" -e \"SELECT * FROM events ORDER BY recorded_at DESC LIMIT 20;\""

db-tables:
	docker compose exec db bash -c "mysql --default-character-set=utf8mb4 -u \"$$MYSQL_USER\" -p\"$$MYSQL_PASSWORD\" \"$$MYSQL_DATABASE\" -e \"SHOW TABLES;\""

shell:
	docker compose exec app bash