</main>

    <!-- Footer section // Sekcija podnožja -->
    <footer class="bg-dark text-white py-3 mt-4">
        <div class="container text-center">
            <!-- Display system name and current year // Prikaži naziv sistema i trenutnu godinu -->
            <p class="mb-0">
                <?php echo $L['pet_shelter_management_system'] ?? 'Pet Shelter Management System'; // System name // Naziv sistema ?>
                &copy; <?php echo date('Y'); // Current year // Trenutna godina ?>
            </p>
        </div>
    </footer>

    <!-- Bootstrap JS // Bootstrap skripta -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS // Prilagođena skripta -->
    <script src="assets/js/script.js"></script>
</body>
</html>