# PHP email tokens

Used in password reset (or sign up verification) emails, these need to be:

1. Entirely random
2. Short, containing only simple ([0-9, A-Z and a-z](https://www.wikidata.org/wiki/Q809817)) characters (to avoid email problems)
3. Expiring within a short period of time (though still dependent on security of users mailbox)
4. Deleted once used and/or expired (this bit is down to you!)
5. Hashed when stored in the database (like passwords, so useless if read via SQL injection or worse)

### Sample code for *forgot_password.php*

```
$token = new PasswordToken;
$token->getEmailToken(); // include in the link you email the user (don't store anywhere!)
$token->getDatabaseHash(); // store against the user (128 character string) along with `tokenCreated`
```

*Tip: better to put the user in a queue, then generate tokens/emails in a worker/cron.*

### Sample code for *reset_password.php*

```
$token = new PasswordToken;
$user  = loadFromHash($token->hashFromToken($_GET['token'])); // loadFromHash() is pseudo code, your bit!

if ($user && $token->stillValid($user->tokenCreated)) { // DateTime/Carbon parameter (or validate in your SQL query)
    // show password form, delete hash/expiry stored against the user
} else {
    // show generic/non-revealing 'Sorry, that token is no longer valid' message  
}
```

### Options

An array can be passed in the constructor to override defaults:

- **Token expiry period:** the **15 minute** default allows for email delivery delays, but lowers the risk of emails sitting around in a possibly unattended email client
- **Token length:** the **24 character** default is nice and short for emails, but gives ~10,000,000,000,000,000,000,000,000,000,000,000,000,000,000 combinations for the 62 case-sensitive alphanumeric characters used - impossible to brute-force successfully ([20 or more is recommended](https://stackoverflow.com/questions/20013672/best-practice-on-generating-reset-password-tokens))

```
new PasswordToken(['expiryMinutes' => 60]);
new PasswordToken(['tokenLength' => 30]);
new PasswordToken(['expiryMinutes' => 60, tokenLength' => 30]);
```

### Helpers

There are two helper functions:

```
$token->getExpiryMinutes(); // useful to mention in your email message
$token->getTokenLength(); // not sure what you'd use this for!
```

## Credits

[Comments](https://stackoverflow.com/questions/20013672/best-practice-on-generating-reset-password-tokens), [advice](https://security.stackexchange.com/questions/86913/should-password-reset-tokens-be-hashed-when-stored-in-a-database) and [code](https://security.stackexchange.com/questions/86913/should-password-reset-tokens-be-hashed-when-stored-in-a-database) from [Martin Stoeckli](https://www.martinstoeckli.ch/) were invaluable in getting my knowledge and understanding to the point of being happy with `PasswordToken` - thanks Martin! :-)