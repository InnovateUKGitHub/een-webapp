
#Install project locally
```
git clone https://bitbucket.org/aerian/een.git
git checkout master
git checkout develop
make install
```


#Track core version of Drupal
```
git remote add -t 8.1.x upstream http://git.drupal.org/project/drupal.git
git checkout -b upstream upstream/8.1.x
```

#Update Drupal core

```
git pull -s subtree upstream 8.1.x
git push origin
```