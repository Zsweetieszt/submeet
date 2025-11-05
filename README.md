# Requirement

- PHP 8.2 - 8.4
- Laravel 12
- Docker (Optional if u want use docker)
- PostgreSQL 17

# Other Tech

- Core UI - Bootstrap

## Tahapan Install Requirement:

1. **Install Composer:**
   - https://getcomposer.org/download/

2. **Install Docker (Optional):**
   - https://docs.docker.com/engine/install/

1. **Install PostgreSQL (Jika tidak ingin menggunakan docker):**
   - https://www.postgresql.org/download/

## Tahapan Install Project:

1. **Clone Repository:**
     ```
     git clone https://gitlab.com/tmfadhi12/submeet.git 
     ```
2. **Masuk ke Path Project:**
3. **Composer Install:**
   - Jalankan perintah
     ```
     composer install
     ```

5. **File .env**
   - copy env.example dan rename file tersebut menjadi .env
   - Sesuaikan konfigurasi database anda
   - Jika ingin running menggunakan docker gunakan konfigurasi
   ```
    DB_CONNECTION=pgsql
    DB_HOST=db 
    DB_PORT=5432
    DB_DATABASE=submeet
    DB_USERNAME=submeetdb
    DB_PASSWORD=secret
   ```
    - Jika ingin running dengan local DB, comment/nonaktifkan konfigurasi diatas
    - Lalu gunakan konfigurasi berikut dengan cara uncomment baris 31-36
    
    ```
    # DB_CONNECTION=pgsql
    # DB_HOST=localhost 
    # DB_PORT=5432
    # DB_DATABASE=submeet
    # DB_USERNAME=postgres
    # DB_PASSWORD=

    ```
    (Sesuaikan kembali dengan konfigurasi db local anda)

(Jika menggunakan docker lakukan step berikut!)

1. **Build Image**
    - jalankan perintah berikut dan tunggu hingga proses selesai
    ```
     docker compose up -d --build (pertama kali run)
     docker compose up -d (run kedua dan selanjutnya, kecuali terdapat update yang dibutuhkan untuk
     build ulang, gunakan perintah yang pertama)
    ```
2. **Pastikan seluruh container berjalan**
    - cek masing-masing log image container
    - jika semuanya telah hijau maka telah berhasil dan dapat langsung digunakan
    - update pada kode akan terupdate secara real time sehingga tidak perlu restart image container

(Jika tidak menggunakan docker lakukan step berikut!)

1. **Generate Key**
   - Generate Project Key dengan cara:
    ```
     php artisan key:generate
     ```

2. **Migrasi**
   - Lakukan migrasi database
   ```
   php artisan migrate

   ```

3. **Seeding***
   - Lakukan seeding untuk mengisi initial data di database
   ```
   php artisan db:seed
   ```

4. **Menghubungkan dengan folder storage**
   ```
   php artisan storage:link
   ```

5. **Jalankan Aplikasi***
   - Anda dapat menjalankan aplikasi dengan cara
   ```
   php artisan serve
   ```

Notes: Selebihnya baca intruksi pada .env.example untuk konfigurasi.