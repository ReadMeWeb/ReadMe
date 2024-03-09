function parsepath () {
        sed 's/\//\\\//g' <<< $*
}

function setincludepath() {
        find . \
                -type f \
                -name '*.php' \
                -exec sed -i 's/set_include_path(.*);/set_include_path('"'$(parsepath $*)'"');/' '{}'  \;
}

function setpagepath(){
        sed -i 's/const path = .*;/const path = '"'$(parsepath $*)'"';/' include/pages.php
}

function setmysqllogin(){
        sed -i "s/private const HOST = '.*';/private const HOST = 'localhost';/" include/database.php
        sed -i "s/private const USERNAME = '.*';/private const USERNAME = '$1';/" include/database.php
        sed -i "s/private const PASSWORD = '.*';/private const PASSWORD = '$2';/" include/database.php
        sed -i "s/private const DATABASE = '.*';/private const DATABASE = '$1';/" include/database.php
}

setincludepath ''         # esempio: '/home/< VOSTO NOME UTENTE >/public_html/orchestra/'
setpagepath ''            # esempio: '/< VOSTO NOME UTENTE >/orchestra/'
setmysqllogin '' '' '' '' # esempio: '< VOSTO NOME UTENTE >' '< LA VOSTRA PASSWORD DI MYSQL >'

