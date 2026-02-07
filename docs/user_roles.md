# User Roles & Permissions 👥

Laracloak follows a **"Default Deny"** security model. By default, a user has no access to any page or administrative function unless explicitly granted through their group memberships.

## 🛡️ "Default Deny" Principle

This model ensures maximum security:
1.  **Strict Isolation**: A newly created user sees an empty dashboard.
2.  **Explicit Permissioning**: Admins must manually assign users to groups that have access to specific pages.
3.  **Minimalist Approach**: Users only see what they need to do their job.

## 📂 Groups and Categories

Permissions are managed collectively through **Groups**.

### Groups
A Group is a collection of users who share the same access level.
*   **Administrative Access**: Grants access to the `/panel` to manage users, groups, and pages.
*   **Page Access**: Defines which Forms or Dashboards the group can view or edit.

### Categories
Categories are used to organize pages within the sidebar. Groups can be granted access to an entire category or to individual pages within it.

## 🔐 Permission Types

When assigning a Page or Category to a Group, you can choose between:

*   **View**: The user can see the page and interact with its designated primary function (e.g., submit a form, view data).
*   **Edit**: (Only for administrative users) Allows modifying the page configuration through the Visual Editor.

## 🛠️ Best Practices

1.  **The "Least Privilege" Rule**: Only grant the minimum permissions necessary for a user's role.
2.  **Functional Groups**: Create groups based on function (e.g., "Sales Team", "HR Managers") rather than naming them after individuals.
3.  **Audit Logs**: Use the system logs to monitor who accesses or modifies sensitive configurations.
