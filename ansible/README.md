Création d'une machine virtuele VirtualBox Debian 12 pour CGI-CODIF 

Prérequis :
- Python 3
- git
- Ansible
- un fichier image ISO de DVD Debian 12

Création de la VM VirtualBox :
- Cliquer sur Nouvelle
- Indiquer le nom de la VM (par exemple "cgicodif"), l'image ISO (par exemple debian-12.14.0-amd64-DVD-1.iso), cocher "Skip Unattended Installation".
- Cliquer sur "> Hardware" et indiquer : Mémoire vive 4096 MB, 2 CPU
- Cliquer sur "> Hard disk" et indiquer : 30 Gio
- Cliquer sur Finish

Installation du système :
- Double cliquer sur la VM dans la colonne de gauche, la VM démarre et une fenêtre s'ouvre.
- Sélectionner "Graphical install".
- Select a language : sélectionner "French - Français".
- Choix de votre situation géographique : sélectionner "France".
- Configurer le clavier : sélectionner "Français".
- Indiquer le nom du système, par exemple "cgicodif".
- Laisser le domaine proposé par défaut.
- Choisir un mot de passe de root.
- Indiquer le nom de l'utilisateur
- Laisser l'identifiant proposé par défaut (ou le modifier éventuellement).
- Choisir un mot de passe.
- Partitionner les disques : sélectionner "Assisté - utiliser un disque entier".
- Laisser le disque proposé par défaut.
- Sélectionner "Tout dans une seule partition".
- Sélectionner "Terminer le partitionnement et appliquer les changements".
- Faut-il appliquer les changements sur les disques ? Sélectionner "Oui".
- Faut-il analyser d'autres supports d'installation ? Sélectionner "Non".
- Faut-il utiliser un miroir sur le réseau ? Sélectionner "Oui" puis "France" puis "deb.debian.org".
- Mandataire http : taper "http://proxy.infra.dgfip:3128".
- Souhaitez-vous participer à l'étude statistique sur l'utilisation de paquets ? Sélectioner "Non".
- Sélection des logiciels : décocher "environnement de bureau Debian" et "Gnome", cocher "serveur web" et "serveur ssh" et laisser coché "utilitaires usuels du système".
- Installer le programme de démarrage GRUB sur le disque principal ? Sélectionner "Oui".
- Périphérique où sera installé le programme de démarrage : sélectionner /dev/sda.
- Redémarrer.

Configuration réseau :
- Dans VirtualBox, sélectionner la VM, cliquer sur Configuration puis Réseau, bouton "Redirection de ports".
- Ajouter une règle pour le port invité 22 et une règle pour le port invité 80. Indiquer les ports hôtes choisis (différents) et laisser les autres champs par défaut.

Configuration ssh :
- Se connecter sous root dans la console VirtualBox.
- Dans /etc/ssh/sshd_config, ajouter la ligne "PermitRootLogin yes".
- Redémarrer sshd avec la commande "service sshd restart".
- Tester la connexion à partir du poste de travail avec la commande "ssh root@127.0.0.1 -p xxxx" où xxxx est le port hôte associé au port invité 22.
- Partager les clés ssh avec la commande "ssh-copy-id -p xxxx root@127.0.0.1".
- Dans le fichier inventory.ini, indiquer le port hôte associé au port invité 22 (ansible_port=xxxx).
- Tester la connexion par Ansible avec la commande "ansible -m ping all".

Configuration apt :
- Dans une session ssh sur la VM, désactiver le cdrom en commentant la première ligne du fichier /etc/apt/sourcfes.list :
#deb cdrom:[Debian GNU/Linux 12.14.0 _Bookworm_ - Official amd64 DVD Binary-1 with firmware 20260516-14:43]/ bookworm contrib main non-free-firmware

Exécution du playbook : ansible-playbook playbook.yml

Création de la base de données :
- Copier un dump de la base sur la VM
- Dans une session ssh sur la VM, créer la base de données :
    CGI-CODIF4 ~ # su postgres
    postgres@cgicodif4:~$ psql
    psql (15.19 (Debian 15.19-0+deb12u1))
    Saisissez « help » pour l'aide.

    postgres=# create database cgicodif;
    CREATE DATABASE
    postgres=# exit
    postgres@cgicodif4:~$ 
- Charger le dump :

Déploiement de l'application :
- Se connecter par ssh à la VM :
    CGI-CODIF4 ~ # cd /data/cgicodif
    CGI-CODIF4 /data/cgicodif # git clone https://forge.dgfip.finances.rie.gouv.fr/dgfip/esi-montreuil/cgicodif_v2.git .
    Clonage dans '.'...
    Username for 'https://forge.dgfip.finances.rie.gouv.fr': jbailhache
    Password for 'https://jbailhache@forge.dgfip.finances.rie.gouv.fr': 
    remote: Enumerating objects: 7196, done.
    remote: Counting objects: 100% (8/8), done.
    remote: Compressing objects: 100% (8/8), done.
    remote: Total 7196 (delta 0), reused 0 (delta 0), pack-reused 7188 (from 1)
    Réception d'objets: 100% (7196/7196), 68.90 Mio | 29.85 Mio/s, fait.
    Résolution des deltas: 100% (3964/3964), fait.
    CGI-CODIF4 /data/cgicodif # 

Configuration de l'application :
- Copier le fichier env.ini dépendant de l'environnement dans le répertoire /data/cgicodif/common de la VM.

Ci-dessous le contenu du fichier env.ini où les secrets ont été remplacés par **** :

;===========================================================================
; Configuration propre à l'environnement courant
; Les commentaires commencent par ';', comme dans php.ini
;===========================================================================

;===========================================================================
; PARAMETRES DE CONNEXION A LA BASE LOCALE
;---------------------------------------------------------------------------
[db]
host = "localhost"
name = "cgicodif"
username = "cgicodif"
password = "****"

;===========================================================================
; CONFIG LDAP DAC
;---------------------------------------------------------------------------
[ldap]
host= "annuaire-vip-ldap.ira.infra.dgfip"
root_dn = "cn=CGICODIF,ou=applications,ou=dgfip,ou=mefi,o=gouv,c=fr"
root_password = "****"


;===========================================================================
; REPERTOIRE DE L'APPLICATION
;---------------------------------------------------------------------------
[directory]
app_dir = ""

;===========================================================================
; API Légifrance
;---------------------------------------------------------------------------
[api]
proxy = "http://proxy-appli.infra.dgfip:8080"
oauth_url = "https://sandbox-oauth.piste.gouv.fr/api/oauth"
api_url = "https://sandbox-api.piste.gouv.fr/dila/legifrance/lf-engine-app"
client_id = "****"
client_secret = "****"

