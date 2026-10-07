# Agency SaaS Workflow

This document outlines the complete lifecycle of how work flows through the application, from the moment a client requests a service to the final invoice.

## The Cast of Characters
*   **Owner / Admin (The Agency)**: They manage the business, assign work, and bill the clients.
*   **Member (The Worker)**: They execute the tasks assigned to them by the agency.
*   **Client (The Customer)**: They request work, review deliverables, and pay invoices.

---

## The Complete Workflow

### Phase 1: The Request (Client -> Agency)
1.  **Client Logs In**: The client accesses their portal and navigates to the **Service Requests** tab.
2.  **Submit Request**: The client clicks "New Request" and fills out a form (e.g., *"I need a new landing page designed"*).
3.  **Agency Notification**: The request appears in the Owner/Admin's Service Requests queue with a `pending` status.

### Phase 2: Triage & Conversion (Agency)
1.  **Review**: The Admin reviews the request. They can optionally send a message back to the client (e.g., *"We are looking into this now!"*) and mark it as `reviewed`.
2.  **Convert to Work**: The Admin decides to accept the work. They click **Convert to Work**.
3.  **Project Creation**: The Admin converts the request into a new **Project** called "Landing Page Design". The system automatically assigns the Client to this new project. The original request is marked as `converted`.

### Phase 3: Execution (Agency & Members)
1.  **Task Breakdown**: Inside the new "Landing Page Design" project, the Admin creates several **Tasks**:
    *   *Task 1: Wireframes* (Assigned to Member A)
    *   *Task 2: Copywriting* (Assigned to Member B)
2.  **Doing the Work**: Member A logs in, sees *Task 1* in their queue, and changes the status from `todo` to `in_progress`.
3.  **Internal Review**: Member A finishes the wireframes and changes the status to `review`.

### Phase 4: Client Approval (Agency -> Client)
1.  **Request Sign-off**: The Admin reviews Member A's work. It looks good, so the Admin clicks **Request Approval** on the task.
2.  **Client Review**: The Client logs in, goes to the Project, and sees that *Task 1* is "Pending Approval".
3.  **The Decision**:
    *   *Scenario A (Rejection)*: The client clicks **Reject** and leaves feedback: *"Make the logo bigger."* The task automatically goes back to `in_progress` for Member A to fix.
    *   *Scenario B (Approval)*: The client clicks **Approve**. The task automatically moves to `done`.

### Phase 5: Billing (Agency -> Client)
1.  **Generate Invoice**: Once all tasks in the project are `done`, the Admin goes to the **Invoices** tab and clicks "New Invoice".
2.  **Drafting**: The Admin selects the Client from the dropdown, links the "Landing Page Design" project, adds the line items (e.g., "Wireframes - $500"), and saves it. The invoice is created with a `draft` status.
3.  **Sending**: The Admin reviews the draft and changes the invoice status to `sent`. (In a fully built system, this would trigger an email to the client).
4.  **Client Review**: The Client logs in, navigates to their **Invoices** tab, and sees the new invoice marked as `sent`. They can click to view the full PDF/details.
5.  **Payment**: The Client pays the invoice (either externally or via a payment link if integrated). 
6.  **Reconciliation**: The Admin marks the invoice as `paid`. The Client's dashboard updates to reflect the paid status.

---

## Summary of Role Permissions

| Feature | Owner / Admin | Member | Client |
| :--- | :--- | :--- | :--- |
| **Service Requests** | Review, Reject, Convert | Cannot view | Create, View own requests |
| **Projects** | Create, Edit, View all | View all | View only assigned projects |
| **Tasks** | Create, Assign to anyone, Change any status | Create (auto-assigned to self), Change status of own tasks | View tasks on assigned projects |
| **Approvals** | Request client approval | Cannot request approval | Approve/Reject requested tasks |
| **Invoices** | Create, Edit, Change status | Cannot view | View only own invoices |
