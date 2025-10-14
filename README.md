# Pet Shelter

## ENGLISH

**Pet Shelter** is a web application for managing animal shelter data, built with PHP and MySQL. It provides a comprehensive system for tracking pets, vaccinations, health checks, and user management.

### Features

- **Pet Management**: View, add, edit, and delete pet records with advanced filtering and searching
- **Vaccination Tracking**: Add and edit vaccination records for pets
- **Health Check Management**: Record and manage health check information
- **User Authentication**: Login/register system with role-based access
- **User Roles**: Admin, Staff (full access), and basic users (limited access)
- **Multilingual Support**: English and Serbian language support
- **Pagination & Sorting**: Efficient display of large datasets with customizable sorting
- **Export Functionality**: Export pet data to XLS and PDF formats
- **Image Upload**: Upload and display pet profile images
- **Responsive Design**: Bootstrap 5 responsive interface with modals
- **Dashboard**: User dashboard for navigation and overview

### Technologies

- PHP 7+
- MySQL/MariaDB
- Bootstrap 5
- JavaScript/jQuery (for dynamic UI elements)
- XAMPP/LAMP/WAMP local server environment

### Installation

1. **Clone the repository:**
    ```sh
    git clone https://github.com/ivicamilic/pet-shelter.git
    ```
2. **Place in your web root directory** (e.g. `c:\xampp\htdocs\pet-shelter`)
3. **Create the database:**
   - Create a database named `jkpmedia_zoo` in MySQL
   - Import database structure using the provided SQL scripts or via phpMyAdmin
4. **Configure database connection in `includes/config.php`:**
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');     // Update with your DB user
    define('DB_PASS', '');         // Update with your DB password
    define('DB_NAME', 'jkpmedia_zoo');
    ```
5. **Set up uploads directory permissions:**
   - Ensure `uploads/pets/` directory is writable (755 or 777 for testing)
6. **Launch the application:**
   - Access via: `http://localhost/pet-shelter/`
   - Default login: Register an account or use existing admin credentials

### Project Structure

- **`index.php`** - Entry point, redirects to login or pets page
- **`login.php`** / **`register.php`** - User authentication
- **`dashboard.php`** - User dashboard
- **`pets.php`** - Main pets listing with pagination, search, and sorting
- **`view-pet.php`** - Detailed pet view with vaccinations and health checks
- **`edit-pet.php`** - Edit pet information, vaccinations, and health data
- **`add-pet.php`** - Add new pets
- **`add-vaccination.php`** / **`edit-vaccination.php`** - Vaccine management
- **`add-health-check.php`** / **`edit-health-check.php`** - Health check management
- **`delete-pet.php`** - Delete pets (admin/staff only)
- **`users.php`** / **`edit-user.php`** / **`delete-user.php`** - User management (admin only)
- **`export.php`** - Export functionality (XLS/PDF)
- **`minimal-view.php`** / **`working-view.php`** / various `*view.php` - Alternative display views
- **`includes/`**
  - `config.php` - Database and general configuration
  - `auth.php` - Authentication functions
  - `functions.php` - Helper functions
  - `db.php` - Database connection class
  - `header.php` / `footer.php` - Page templates
- **`lang/`** - Language files (en.php, sr.php)
- **`assets/`** - Static assets (CSS, JS, images)
- **`uploads/pets/`** - Pet image uploads
- **`MySQL baza jkpmedia_zoo.txt`** - Database migration/example queries

### Usage

- **Login/Register**: Access the system with user accounts
- **Dashboard**: Overview of available actions based on user role
- **Pets Management**: Navigate through pets with search, pagination, and filtering
- **Admin Features**: Full CRUD operations on pets, users, and system data
- **Staff Features**: Limited CRUD on pets (no user management)
- **Export**: Download pet lists in XLS or PDF format

### Notes

- Before publishing, make sure you do not commit sensitive data (passwords, API keys).
- Add `config.php` and similar files to `.gitignore` if needed.
- For production, use proper security measures including HTTPS and secure password hashing.
- The application uses role-based access control with Admin > Staff > User permissions.

### License

MIT

---

## SRPSKI

**Pet Shelter** je veb aplikacija za upravljanje podacima o životinjama u prihvatilištu, izgrađena sa PHP i MySQL. Pruža sveobuhvatan sistem za praćenje ljubimaca, vakcinacija, zdravstvenih pregleda i upravljanje korisnicima.

### Karakteristike

- **Upravljanje ljubimcima**: Pregled, dodavanje, uređivanje i brisanje zapisa o ljubimcima sa naprednim filtriranjem i pretragom
- **Praćenje vakcinacija**: Dodavanje i uređivanje zapisa o vakcinacijama
- **Upravljanje zdravstvenim pregledima**: Evidentiranje i upravljanje zdravstvenim pregledima
- **Autentikacija korisnika**: Sistem prijave/registracije sa ulogama na osnovu pristupa
- **Korisničke uloge**: Admin, Staff (puni pristup) i osnovni korisnici (ograničen pristup)
- **Višejezična podrška**: Engleski i srpski jezik
- **Paginacija i sortiranje**: Efikasan prikaz velikih skupova podataka sa prilagodljivim sortiranjem
- **Funkcionalnost izvoza**: Izvoz podataka o ljubimcima u XLS i PDF formate
- **Upload slika**: Upload i prikaz profilnih slika ljubimaca
- **Responsive dizajn**: Bootstrap 5 responsive interfejs sa modalima
- **Kontrolna tabla**: Korisnička kontrolna tabla za navigaciju i pregled

### Tehnologije

- PHP 7+
- MySQL/MariaDB
- Bootstrap 5
- JavaScript/jQuery (za dinamičke UI elemente)
- XAMPP/LAMP/WAMP lokalno serversko okruženje

### Instalacija

1. **Kloniraj repozitorijum:**
    ```sh
    git clone https://github.com/ivicamilic/pet-shelter.git
    ```
2. **Postavi u svoj web root direktorijum** (npr. `c:\xampp\htdocs\pet-shelter`)
3. **Kreiraj bazu podataka:**
   - Kreiraj bazu podataka pod nazivom `jkpmedia_zoo` u MySQL
   - Importuj strukturu baze koristeći SQL skripte ili phpMyAdmin
4. **Konfiguriši konekciju baze u `includes/config.php`:**
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');     // Ažuriraj svojim DB korisnikom
    define('DB_PASS', '');         // Ažuriraj svojom DB lozinkom
    define('DB_NAME', 'jkpmedia_zoo');
    ```
5. **Postavi dozvole za uploads direktorijum:**
   - Osiguraj da `uploads/pets/` direktorijum bude upisiv (755 ili 777 za testiranje)
6. **Pokreni aplikaciju:**
   - Pristup na: `http://localhost/pet-shelter/`
   - Podrazumevana prijava: Registruj nalog ili koristi postojeće admin akreditive

### Struktura projekta

- **`index.php`** - Ulazna tačka, preusmerava na prijavu ili stranicu ljubimaca
- **`login.php`** / **`register.php`** - Autentikacija korisnika
- **`dashboard.php`** - Kontrolna tabla korisnika
- **`pets.php`** - Glavna lista ljubimaca sa paginacijom, pretragom i sortiranjem
- **`view-pet.php`** - Detaljni prikaz ljubimca sa vakcinacijama i zdravstvenim pregledima
- **`edit-pet.php`** - Uređivanje informacija o ljubimcu, vakcinacijama i zdravstvenim podacima
- **`add-pet.php`** - Dodavanje novih ljubimaca
- **`add-vaccination.php`** / **`edit-vaccination.php`** - Upravljanje vakcinama
- **`add-health-check.php`** / **`edit-health-check.php`** - Upravljanje zdravstvenim pregledima
- **`delete-pet.php`** - Brisanje ljubimaca (samo admin/staff)
- **`users.php`** / **`edit-user.php`** / **`delete-user.php`** - Upravljanje korisnicima (samo admin)
- **`export.php`** - Funkcionalnost izvoza (XLS/PDF)
- **`minimal-view.php`** / **`working-view.php`** / razni `*view.php` - Alternativni prikazi
- **`includes/`**
  - `config.php` - Konfiguracija baze i generalna konfiguracija
  - `auth.php` - Funkcije autentikacije
  - `functions.php` - Pomoćne funkcije
  - `db.php` - Klasa konekcije baze
  - `header.php` / `footer.php` - Šabloni stranica
- **`lang/`** - Jezički fajlovi (en.php, sr.php)
- **`assets/`** - Statički resursi (CSS, JS, slike)
- **`uploads/pets/`** - Upload slika ljubimaca
- **`MySQL baza jkpmedia_zoo.txt`** - Migracije baze/migracioni upiti

### Upotreba

- **Prijava/Registracija**: Pristup sistemu korisničkim nalogom
- **Kontrolna tabla**: Pregled dostupnih akcija na osnovu korisničke uloge
- **Upravljanje ljubimcima**: Navigacija kroz ljubimce sa pretragom, paginacijom i filtriranjem
- **Admin funkcije**: Potpune CRUD operacije nad ljubimcima, korisnicima i sistemskim podacima
- **Staff funkcije**: Ograničene CRUD operacije nad ljubimcima (bez upravljanja korisnicima)
- **Izvoz**: Preuzimanje lista ljubimaca u XLS ili PDF formatu

### Napomene

- Pre objave, uveri se da nisi dodao poverljive podatke (lozinke, API ključeve).
- Dodaj `config.php` i slične fajlove u `.gitignore` ako je potrebno.
- Za produkciju koristi odgovarajuće mere sigurnosti uključujući HTTPS i bezbedno heširanje lozinki.
- Aplikacija koristi kontrolu pristupa na osnovu uloga sa Admin > Staff > Korisnik dozvolama.

### Licenca

MIT

---

**Author / Autor:**  
Ivica Milić
