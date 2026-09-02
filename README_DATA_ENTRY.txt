CRIMINAL INTELLIGENCE SYSTEM - DATA ENTRY V1

This version uses the CURRENT database:
    criminal_intelligence_demo

It does NOT create a new schema.

SETUP
1. Put this folder inside:
   C:\xampp\htdocs\criminal_intelligence

2. Start Apache and MySQL in XAMPP.

3. Keep your existing db.php settings.

4. Open:
   http://localhost/criminal_intelligence/

DATA ENTRY
The dashboard modules now point to:
    data_entry.php?table=<table>

The data-entry page reads the CURRENT table columns from
INFORMATION_SCHEMA and automatically creates a form.

It also detects foreign keys that are actually defined in the
current MySQL schema and renders them as dropdowns.

Supported dashboard tables:
- persons
- cases
- vehicles
- phones
- addresses
- organizations
- cdr
- transactions
- surveillance_events
- social_interactions
- intelligence_reports

WHY THIS APPROACH
Because your project is being built around the current database, the
form should follow the real schema instead of us guessing column names.

NEXT
After confirming that inserts work, we should create specialized forms
for the important relationship tables:
- person_phone
- person_address
- person_organization
- vehicle_owner
- case_person
- case_vehicle
- case_weapon

Those relationship pages are critical for the network-analysis system.
