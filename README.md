# PHP Password Workflow

Looking after passwords in a web app correctly is a complex task - lots of pieces to consider, all of which need to come together for the whole to be secure. This builds on the work of many to make it all a little easier.

'Workflow' covers password reset tokens generated and emailed to users, and ensuring the password entered by a user is sensible.


## Password reset tokens

Done well, these need to be:

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
- **Token length:** the **24 character** default is nice and short for emails, but gives [894,525,125,034,689,530,200 combinations](http://www.statisticshowto.com/calculators/permutation-calculator-and-combination-calculator/) of the 62 case-sensitive alphanumeric characters used ([20 or more is recommended](https://stackoverflow.com/questions/20013672/best-practice-on-generating-reset-password-tokens))

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

## Sensible password checking 

Passwords must be at least 10 characters in length and not be commonly used - there's no means to override this.

Numeric *looking* passwords are rejected, to weed out obvious memorable dates and phone numbers.

All password checks are **case insensitive**.

```
$checker = new PasswordChecker;
$checker->validate('abc123'); // throws PasswordException (too short)
$checker->validate('password123'); // throws PasswordException (too common)
$checker->validate('123-456-7890'); // throws PasswordException (too numeric)
$checker->validate('31/12/1999'); // throws PasswordException (too numeric)
$checker->validate('we love php'); // returns true
```

That's it. Though you can add further (optional, but recommended) checks and restrictions...

### Password reuse

Prevent password reuse by storing previous password hashes in your application and passing them in:

```
$checker = new PasswordChecker;
$checker->setPreviousPasswords($arrayOfHashes); // generated from password_hash()
$checker->validate($userSuppliedPassword);
```

### Password confirmation

If you ask users to confirm their new password, you can pass that in too - simply to have all checks handled consistently:

```
$checker = new PasswordChecker;
$checker->setConfirmation($userSuppliedConfirmation);
$checker->validate($userSuppliedPassword);
```

### User or application obvious

Provide a blacklist of words that are obvious in the context of the user/application. If they're **within the password** (i.e. not necessarily equal to) the user supplied password, validation will fail:

```
$checker = new PasswordChecker(['clem', 'fandango', 'MyAmazingApp');
$checker->validate('myamazingapp'); // throws PasswordException
$checker->validate('myamazingapp123'); // throws PasswordException
$checker->validate('clemfandango'); // throws PasswordException
$checker->validate('fandango123'); // throws PasswordException
```

## Credits

[Comments](https://stackoverflow.com/questions/20013672/best-practice-on-generating-reset-password-tokens), [advice](https://security.stackexchange.com/questions/86913/should-password-reset-tokens-be-hashed-when-stored-in-a-database) and [code](https://security.stackexchange.com/questions/86913/should-password-reset-tokens-be-hashed-when-stored-in-a-database) from [Martin Stoeckli](https://www.martinstoeckli.ch/) were invaluable in getting my knowledge and understanding to the point of being happy with `PasswordToken` - thanks Martin! :-)