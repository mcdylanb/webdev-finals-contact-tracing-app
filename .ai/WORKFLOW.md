## 2. Flowchart Logic

**Goal:** Attendance tracker that tracks both "in" and "out" events.

### Step-by-Step Process Flow

1. **Start:** User arrives at guard house.
2. **Interaction:** User is greeted with an input screen asking for a User ID or if they are a New Visitor.
3. **Decision Point:** `if new?`
* **TRUE (New Visitor):**
* Request the following info:
1. ID Number (If student, faculty, or staff from USC)
2. Complete Name (First Name, Middle Name, Last Name)
3. Complete Address (Barangay, City or Town, Province)
4. Contact Number and Email


* Show information to validate.
* **Decision Point (Button):** `Data Correct: Login / Go Back`
* *FALSE (Go Back):* Routes back to the info request screen.
* *TRUE (Login):* Routes to **Insert table log entry**.




* **FALSE (Returning User):**
* User inputs `AccountID` or `Email / Phone number`.
* **Decision Point:** `Is there an open entry for this user (where DateTime Logout IS NULL)?`
* *TRUE:* **Update logout date** from entry record $\rightarrow$ Route to **Thank You Screen**.
* *FALSE:* Proceed to **Insert table log entry**.






4. **Log Action:** **Insert table log entry** $\rightarrow$ Route to **Thank You Screen**.
5. **End:** **Thank You Screen** loops back to the beginning state ("User arrives at guard house").