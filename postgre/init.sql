-- Buat database jika belum ada
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT FROM pg_database WHERE datname = '${DB_DATABASE}') THEN
        CREATE DATABASE "${DB_DATABASE}";
    END IF;
END $$;

-- Buat user jika belum ada
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_roles WHERE rolname = '${DB_USERNAME}') THEN
        CREATE ROLE "${DB_USERNAME}" WITH LOGIN PASSWORD '${DB_PASSWORD}';
    END IF;
END $$;

-- Berikan semua hak akses ke user pada database yang dibuat
GRANT ALL PRIVILEGES ON DATABASE "${DB_DATABASE}" TO "${DB_USERNAME}";
