# Page snapshot

```yaml
- link:
  - /url: /
  - img
- text: Email
- textbox "Email": test@example.com
- list:
  - listitem: These credentials do not match our records.
- text: Password
- textbox "Password": password
- checkbox "Remember me"
- text: Remember me
- link "Forgot your password?":
  - /url: http://localhost:8000/forgot-password
- button "Log in"
```