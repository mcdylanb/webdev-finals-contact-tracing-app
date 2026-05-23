## 1. database_schema 

### Table: `contacts`

* **PK: id** (Primary Key)
* last_name
* first_name
* middle_name
* barangay
* city
* province
* phone_number
* email
* usc_id_number

### Table: `logs`

* **PK: entry_id** (Primary Key)
* **FK: id_number** (Foreign Key referencing `contacts.id`)
* datetime_login
* datetime_logout

### Table: `admin`

* **PK: admin_id** (Primary Key)
* name
* username
* password

### Relationships

* `contacts.id` **(1)** $\rightarrow$ **(N)** `logs.id_number` *(One contact can have multiple log entries)*

---

