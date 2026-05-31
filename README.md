# AccountApproval — OSSN 8.9 Component

A lightweight moderation component for [Open Source Social Network (OSSN)](https://www.opensource-socialnetwork.org/) that gives admins control over new user activity without blocking access entirely.

## What it does

| Status   | Can Login | Can Read | Can Post / Comment / Message / Upload |
|----------|-----------|----------|----------------------------------------|
| Pending  | ✅ Yes    | ✅ Yes   | ❌ No                                  |
| Approved | ✅ Yes    | ✅ Yes   | ✅ Yes                                 |
| Banned   | ❌ No     | ❌ No    | ❌ No (handled by OSSN core)           |

Every new registration is automatically set to **Pending** after email validation. No core file edits required. Survives all OSSN updates.

---

## Why use this?

Legitimate users can verify their registration worked, complete their profile, and browse content — but illegitimate users cannot spam the site with posts, comments, messages, or file uploads until an admin approves them. This helps to stop manual spammers from registering/validating and spamming the news feed etc. New registrants are informed of this at registration so there is no confusion.

---

## Features

- New users automatically set to **Pending** on registration
- Registration success message updated to inform users they can browse but not post until approved (no core files touched — overridden via component locale)
- Pending users see a clear notice when they attempt a restricted action
- Admin approval panel accessible directly from the **User Manager** menu in the admin dashboard
- Approved users receive an automatic email notification
- Admins can set any user back to **Pending** at any time
- No core file edits — survives all OSSN updates

---

## Installation

1. Upload the `AccountApproval` folder to:
   ```
   your-site/components/AccountApproval/
   ```
2. Log in to your OSSN admin panel → **Components** → find **AccountApproval** → click **Enable**.
3. Flush your cache: **Admin → Configure → Cache → Flush Cache**.

---

## Accessing the Approval Manager

In the admin dashboard, go to **User Manager → Approval Manager**.

This opens the approval panel inside the admin dashboard showing all non-admin users sorted by status (Pending first), with **Approve** and **Set Pending** buttons for each user. Approved users receive an automatic email when approved.

---

## Customising the messages

All user-facing text — including the registration notice, the blocked-action message, and the approval email — is in:

```
components/AccountApproval/locale/ossn.en.php
```

Edit any string in that file and flush your cache for changes to take effect. No core files are touched.

---

## Notes for existing users

Users who registered **before** this component was installed have no `approval_status` and are treated as approved — their access is unchanged. Only new registrations from this point on will start as Pending.

---

## Compatibility

- Tested on **OSSN 8.9**
- No core file modifications
- Compatible with future OSSN updates

---

## License

[Open Source Social Network License (OSSN LICENSE)](http://www.opensource-socialnetwork.org/licence)
