# AZBR Online Event Registration

## Deploying to the `dev` site

There is also a development mirror, commonly referred to as _devsite_ that is used for testing and previewing updates before they are pushed live. The dev site is located at https://devreg.azbrscca.org/.

Deploying to the _devsite_ requires `ssh` access to the _1and1_ host.

The `devreg.azbrscca.org` directory is checkout of the git repo that resides on the web server. Checkout the appropriate branch and navigate to http://devreg.azbrscca.org to view the changes.

For example, if I pushed changes to a branch called `2018-updates`:
```
ssh <USERNAME>@<1AND1.HOST>
cd devreg.azbrscca.org
git checkout 2018-updates
```

***Important Notes***
There are two configuration files, `common/Common.php` and `db/Connection.php` that are not in source control. If these files are removed from the `devreg.azbrscca.org` directory, they must manually be copied over from the `config/registration` directory. Make sure to copy over the files with the `.dev` suffix.

If you edit something and need to reset things to checkout another branch, use `git reset --hard`.

## Deploying to the `live` site

### Set up

Deploying to the _livesite_ requires `ssh` access to the _1and1_ host and a `git remote`.

To set up the git remote, run this command while at the root level of your local checkout of out repo. You will only have to do this once unless you delete and re-clone.

```shell
git remote add livesite ssh://<USERNAME>@<1AND1.HOST><REMOTE_DIR>/git/registration.azbrscca.org/live
```

The _USERNAME_ and _1AND1HOST_ values can be found by logging into the [_1and1_ administrative console](https://account.1and1.com/). The _REMOTE_DIR_ value is the output of the `pwd` command after a successful `ssh` login the web server.

After the `git remote` command is run, `.git/config` should have a block like this:

```
[remote "livesite"]
        url = ssh://********@********.host/********/git/registration.azbrscca.org/live
        fetch = +refs/heads/*:refs/remotes/livesite/*
```

### Deploying

After the desired changes have been landed on the `master` branch, checkout and update `master`.

```
git checkout master
git pull
```

Then, push to _livesite_.

```
git push livesite
```

That's it! The new changes will be pushed to a mirror of the git repo on the web server. Then the repo will be checked out, overwriting the existing `registration.azbrscca.org` subdirectory. The [git `post_receive` hook](https://git-scm.com/docs/githooks#post-receive) will run the `configure.php` script. Browse to http://registration.azbrscca.org/ and check out the changes.

## Database changes

The site uses a JSON file as a source of truth for data types the mySQL database contains. On the _1and1_ host, there is a `databases` subdirectory containing a separate JSON file for the dev site and the live site. If this file is missed, the site will generate it on the next user request made to the site.

If deploying to the _devsite_ with database changes, manually remove this file and reload any page on the site to regenerate it. Otherwise, the site will not accurately reflect the database changes.

On the _livesite_, this file is automatically removed as part of the `post-receive` hook when a deployment takes place.
