/* SELECT * FROM Collectivite WHERE id=1; */
INSERT INTO Collectivite (id, nom, domain, image, message, active, textmailnew, textmailrefuse, textmailwalid, abscissesVisa, ordonneesVisa, abscissesSignature, ordonneesSignature, couleurVisa, titreVisa, pageSignature, deleteClasseurAfter) VALUES (1, 'Sictiam', 'sictiam', '5fc346b1845dcddeb2d58f38847a57c46d3f447f.jpeg', '<p>Le parapheur &eacute;lectronique S.E.SI.LE, <strong>Syst&egrave;me Electronique de SIgnature LEgale</strong>, vous offre l&rsquo;opportunit&eacute; de d&eacute;poser vos fichiers afin de proc&eacute;der &agrave; leur validation selon des circuits d&eacute;finis mais &eacute;galement de les <strong>signer &eacute;lectroniquement</strong>.<br /><br />A noter : les flux <strong>PES</strong> peuvent &ecirc;tre sign&eacute;s via le parapheur en utilisant le format d&eacute;di&eacute; XaDES normalis&eacute; par la DGFIP<br /><br /></p>
<h4><u>MESSAGE D''ALERTE :</u></h4>
<p>Nous vous informons que la nouvelle mise &agrave; jour de <strong>Google Chrome (version 43)</strong> <strong>ne permet plus l&rsquo;authentification par un certificat &eacute;lectronique sur SESILE.</strong> <br /><br />N&eacute;anmoins, la <strong>signature d&rsquo;un flux PES&nbsp;</strong>reste et restera possible sur <strong>Google Chrome jusqu&rsquo;&agrave; la version 45</strong>. Au-del&agrave;, vous devrez n&eacute;cessairement utiliser un autre navigateur Internet (Mozilla Firefox, Internet Explorer). <br /><br />Nous nous tenons &agrave; votre disposition pour toutes questions compl&eacute;mentaires aux coordonn&eacute;es suivantes :<br />- T&eacute;l : 04 92 96 80 80<br />- Mail : demat@sictiam.fr<br /><br />Cordialement,<br /><br /><em>L''&eacute;quipe D&eacute;mat&eacute;rialisation</em><br /><br /><br /><br /><br /></p>', 1, '<p>Bonjour {{ validant }},<br /><br />Un nouveau classeur <strong>{{ titre_classeur }}</strong> vient d''&ecirc;tre d&eacute;pos&eacute; par {{ deposant }} <br /> <br /> Il convient de le valider avant le<strong> {{ date_limite | date(''d/m/Y'') }}</strong>.<br /><br />Vous pouvez visionner le classeur {{lien|raw}}.<br /><br /></p>', '<p>Bonjour {{ deposant }}, <br /><br />Le classeur {{ titre_classeur }} vient d''&ecirc;tre refus&eacute; par {{ validant }} pour le motif suivant: <br /> {{ motif }} <br /><br /> Vous devez y apporter les modifications n&eacute;cessaires avant de le soumettre &agrave; nouveau <br /> Vous pouvez visionner le classeur {{lien|raw}}<br /><br /></p>
<p>&nbsp;</p>', '<p>Bonjour {{ deposant }},<br /><br />Le classeur "{{ titre_classeur }}" vient d''&ecirc;tre <strong>valid&eacute; </strong>par {{ validant }} <br /> <br /> Vous pouvez visionner le classeur {{lien|raw}}.<br /><br /></p>', 10, 10, 123, 253, '#454545', 'VISE PAR :', 0, 180);

/* SELECT * FROM User WHERE collectivite=1 AND id=2; */
INSERT INTO User (id, username, username_canonical, email, email_canonical, enabled, salt, password, last_login, confirmation_token, password_requested_at, roles, Nom, Prenom, path, ville, code_postal, pays, departement, role, apitoken, apisecret, apiactivated, collectivite, pathSignature, qualite, sesile_version) VALUES (1, 'legacy@sictiam.fr', 'legacy@sictiam.fr', 'legacy@sictiam.fr', 'legacy@sictiam.fr', 1, '59du74lhbh0c0kw0cw004g4wkkgssw4', '60cBrdEYc30Ck0wAmBzZelMmnCqYZDsApBu87RxYihnVUlGms/WsA+jA01kfvsG9NoSywAAYrNWklR1EyZyDxg==', '2018-05-29 13:42:54', null, null, 'a:1:{i:0;s:16:"ROLE_SUPER_ADMIN";}', 'LEGACY', 'User', 'fe5ab4cfa1106d844d669861c98232aa361f7cdf.jpeg', 'VALLAURIS', '06220', 'FRANCE', 'Alpes-Maritimes', 'Déposant', 'token_c42d3567147800554cd7b972a59a0fca', 'secret_c81d6b14f97db6d1c771aca893f3f09a', 1, 1, null, null, 3.5);

/* SELECT * FROM UserPack WHERE collectivite=1; */
INSERT INTO UserPack (id, collectivite, nom, creation) VALUES (1, 1, 'SDDAN - FINANCE', '2017-05-03 09:32:13');
INSERT INTO UserPack (id, collectivite, nom, creation) VALUES (2, 1, 'Administratif', '2017-07-28 09:49:24');

/* SELECT * from userpack_user WHERE userpack_id=44 AND user_id=10; */
INSERT INTO userpack_user (userpack_id, user_id) VALUES (1, 1);

/* SELECT * FROM Groupe WHERE collectivite=1 and id=16 */
INSERT INTO Groupe (id, Nom, collectivite, couleur, json, ordreEtape, creation) VALUES (1, 'DEMAT', 1, 'white', '{"name":"Pierre PINTARIC","id":"9","color":"white","children":[{"name":"Benoit COLINET","id":"3","color":"white","children":[{"name":"Anne Sophie LEVEQUE","id":"2","color":"white"},{"name":"BAUMANN Laura","id":"113","color":"white"}]}]}', '26,27', null);
/* SELECT * FROM EtapeGroupe WHERE groupe=16 */
INSERT INTO EtapeGroupe (id, groupe, ordre) VALUES (1, 1, 0);
INSERT INTO EtapeGroupe (id, groupe, ordre) VALUES (2, 1, 1);

/* SELECT * from etapegroupe_user WHERE etapegroupe_id=1 AND user_id=10 */
INSERT INTO etapegroupe_user (etapegroupe_id, user_id) VALUES (1, 1);
/*  */
/*  */
/*  */
/*  */
/*  */
