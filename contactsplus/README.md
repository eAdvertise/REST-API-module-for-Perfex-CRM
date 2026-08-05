# ContactsPlus (Perfex CRM Module)

Features:
- Δημιουργία επαφής χωρίς email (χωρίς portal access).
- Σύνδεση μίας επαφής με πολλαπλές εταιρείες με ρόλους/flags.
- Επιλεκτική ενεργοποίηση portal access (δημιουργία core tblcontacts) per εταιρεία.

Install:
1. Αντιγράψτε τον φάκελο `contactsplus` στον φάκελο `modules/` του Perfex.
2. Από Admin → Setup → Modules, ενεργοποιήστε το "Contacts+".
3. Το module προσθέτει tab περιεχομένου στην καρτέλα Πελάτη.

Notes:
- Δεν αλλάζει core schema. Όλα τα δεδομένα είναι σε `pmc_*` πίνακες.
- Για portal access χρησιμοποιείται το core `clients_model->add_contact`.
