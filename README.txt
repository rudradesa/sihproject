CRIMINAL INTELLIGENCE SYSTEM - DASHBOARD V1

1. Extract this folder into:
   C:\xampp\htdocs\criminal_intelligence

2. Start Apache and MySQL in XAMPP.

3. Make sure your existing database is:
   criminal_intelligence_demo

4. Edit db.php if your MySQL root password is not empty.

5. Open:
   http://localhost/criminal_intelligence/

This version uses the CURRENT database tables.
It does not create, drop, or modify your database.

The dashboard reads COUNT(*) from the existing tables and displays
the current number of records.

Next development step:
- Build the Persons module against the existing persons table.
- Then build related modules while respecting foreign keys.
