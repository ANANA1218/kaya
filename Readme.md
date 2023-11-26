Modification des rôles des utilisateurs
Par défaut, lorsqu'un nouvel utilisateur est créé dans le système, son rôle est défini en tant qu'utilisateur standard (ROLE_USER). Pour accorder des privilèges administratifs à un utilisateur et lui donner accès à des fonctionnalités spécifiques destinées aux administrateurs, vous devrez ajuster son rôle manuellement dans la base de données.

Modification du rôle dans la base de données
Étapes à suivre :
Connexion à la base de données :
Utilisez votre outil de gestion de base de données préféré (tel que phpMyAdmin, MySQL Workbench, etc.) pour accéder à la base de données.

Localisation de la table des utilisateurs :
Recherchez la table contenant les informations des utilisateurs dans la table user. 

Modification du rôle :
Trouvez l'utilisateur pour lequel vous souhaitez ajuster le rôle et localisez la colonne correspondant au rôle de l'utilisateur. Modifiez la valeur de cette colonne de ["ROLE_USER"] à ["ROLE_ADMIN"] pour accorder des privilèges administratifs à cet utilisateur.

Conséquences de la modification
Utilisateur avec le rôle 'user' :
Lorsqu'un utilisateur se connecte avec le rôle ROLE_USER, il accède à la page d'accueil standard sans les fonctionnalités spécifiques à l'administration.

Utilisateur avec le rôle 'admin' :
En se connectant avec le rôle ROLE_ADMIN, l'utilisateur a accès à une page d'administration comportant des fonctionnalités et des privilèges supplémentaires.

Attention
Modifier manuellement les rôles des utilisateurs peut avoir des implications sur les autorisations et les droits d'accès. Il est crucial de bien comprendre les implications de chaque rôle avant de procéder à des modifications.