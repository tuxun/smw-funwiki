window.addEventListener("load", init, false);

// Variables du jeu
var NB_BRIQUES_PAR_LIGNE = 13;
var NB_LIGNES = 8;
var BRIQUE_WIDTH = 28.92;
var BRIQUE_HEIGHT = 24;
var BRIQUE_ESPACE = 2;

var BARRE_WIDTH = 65;
var BARRE_HEIGHT = 8;
var barreY;
var barreX;

var dirBalleX = Math.random()*2-1;
var dirBalleY = -1;
var balleX;
var balleY;
var BALLE_DIAMETRE = 9;
var balleVitesse;
var BALLE_COULEUR = "red";

var tabBriques;
var tabCouleurs;
var tabLevel;
var tabCases;
var context;

var touche; // Touche enfoncée
var nb_touchees;
var nb_cases;
var canvas;
var timerRefresh;
var level;
var nb_levels;
var nb_vies;

function init()
{
	canvas = document.getElementsByTagName("canvas")[0];	
	if(!canvas || !canvas.getContext)
		return;
	
	context = canvas.getContext("2d");	
	if(!context)
		return;

	// On continue
	tabLevel = new Array();
	creer_briques();
	init_inc();
	
	document.getElementsByClassName("jouer")[0].addEventListener("click", demarrer, false);
	window.addEventListener("keydown", deplacer, false);
	window.addEventListener("keyup", stopper, false);
}

function init_inc()
{
	level = 1, nb_vies = 3;
	tabBriques = tabLevel[0];
	nb_touchees = 0, nb_cases = tabCases[0];
	balleVitesse = 1.25;
	barreX = canvas.width/2-BARRE_WIDTH/2;
	barreY = canvas.height-BARRE_HEIGHT-5;
	balleX = canvas.width/2;
	balleY = canvas.height-BARRE_HEIGHT-5-BALLE_DIAMETRE;
	
	document.getElementsByClassName("vies")[0].getElementsByTagName("span")[0].innerHTML = nb_vies + " vies";
	
	nb_level = tabLevel.length;
	context.fillStyle = "#333";
	context.fillRect(barreX, barreY, BARRE_WIDTH, BARRE_HEIGHT);
	refresh();
}

function demarrer()
{
	document.getElementsByClassName("jouer")[0].style.visibility = "hidden";
	timerRefresh = setInterval(refresh, 5);
}

function deplacer(e)
{
	var code = e.keyCode;
	if(code == 37 || code == 39) // Gauche || Droite
		touche = code;
	if(code == 32 && document.getElementsByClassName("jouer")[0].style.visibility == "visible") // Espace
		demarrer();
}
function stopper(e)
{
	var code = e.keyCode;
	if(touche == code)
		touche = 0;
}

function creer_briques(context, nb, nb_lignes, width, height, padding)
{
	tabBriques = new Array(nb_lignes);
	tabCouleurs = new Array(nb_lignes);
	
	for(var i = 0 ; i < nb_lignes ; i++)
	{
		tabBriques[i] = new Array(nb);
		tabCouleurs[i] = "rgb("+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+","+Math.floor(Math.random()*256)+")"; // Couleur aléatoire
		context.fillStyle = tabCouleurs[i];
		
		for(var j = 0 ; j < nb ; j++)
		{
			context.fillRect(j*(width+padding), i*(height+padding), width, height);
			if(j == 0 || j == 9 || i == 5)
				tabBriques[i][j] = 0;
			else
			{
				tabBriques[i][j] = 1;
				nb_cases++;
			}
		}
	}
}

function refresh()
{
	context.clearRect(0, 0, canvas.width, canvas.height);
	
	// Level
	context.fillStyle = "#d6d6d6"
	context.font = '60px Harabara';
	context.fillText("Level "+level, canvas.width/2-78, canvas.height/2);4
	
	for(var i = 0 ; i < NB_LIGNES ; i++)
	{
		context.fillStyle = tabCouleurs[level-1][i];
		for(var j = 0 ; j < NB_BRIQUES_PAR_LIGNE ; j++)
		{
			if(tabBriques[i][j] == 1)
				context.fillRect(j*(BRIQUE_WIDTH+BRIQUE_ESPACE), i*(BRIQUE_HEIGHT+BRIQUE_ESPACE), BRIQUE_WIDTH, BRIQUE_HEIGHT);
		}
	}
	
	// Barre 
	context.fillStyle = "black";
	var vitesse = 2.4;
	if(touche == 37 && barreX >= 0) // Gauche
		barreX -= vitesse;
	else if(touche == 39 && barreX+BARRE_WIDTH <= canvas.width) // Droite
		barreX += vitesse;
	context.fillRect(barreX, barreY, BARRE_WIDTH, BARRE_HEIGHT);
	
	// Choc contre la barre
	if(balleX >= barreX && balleX <= barreX+BARRE_WIDTH+BALLE_DIAMETRE/2 && balleY > barreY-BALLE_DIAMETRE+2 && balleY < barreY+BARRE_HEIGHT-4)
	{
		dirBalleY = -1;
		dirBalleX = 3*(balleX-(barreX+BARRE_WIDTH/2))/BARRE_WIDTH;
	}
	
	// Choc contre les briques
	if(balleY <= NB_LIGNES*(BRIQUE_HEIGHT+BRIQUE_ESPACE)+6 && balleY >= 0)
	{
		// Détecter la case touchée
		var i = parseInt(balleY/(BRIQUE_HEIGHT+BRIQUE_ESPACE));
		var j = parseInt(balleX/(BRIQUE_WIDTH+BRIQUE_ESPACE));
		
		if(i < NB_LIGNES && tabBriques[i][j] == 1)
		{
			nb_touchees++;
			tabBriques[i][j] = 0;
			if(Math.floor(balleY-(balleVitesse-1)) <= i*(BRIQUE_HEIGHT+BRIQUE_ESPACE)) // Touché au dessus + bidouille
				dirBalleY = -1;
			else if(balleY >= (i+1)*(BRIQUE_HEIGHT+BRIQUE_ESPACE)-BRIQUE_ESPACE) // Touché au dessous
				dirBalleY = 1;
			else // Touché sur les côtés
				dirBalleX = -dirBalleX;
		}		
	}
	
	// Choc contre les murs
	if(balleX < 5 || balleX > canvas.width-5)
		dirBalleX = -dirBalleX;
	if(balleY < 5)
		dirBalleY = -dirBalleY;
	else if(balleY > canvas.height+10) // Perdu
	{
		clearInterval(timerRefresh);
		rejouer();
	}
	if(nb_cases == nb_touchees) // Gagné
	{
		clearInterval(timerRefresh);
		continuer();
		return;
	}
	
	// Balle
	balleX += dirBalleX*balleVitesse;
	balleY += dirBalleY*balleVitesse;
	
	// Balle
	context.fillStyle = BALLE_COULEUR;
	context.beginPath();
	context.arc(balleX, balleY, BALLE_DIAMETRE, 0, Math.PI*2, true);
	context.closePath();
	context.fill();
}

function continuer()
{
	level++;
	var play = document.getElementsByClassName("jouer")[0];
	if(level > tabLevel.length) // Gagné
	{
		play.innerHTML = "Gagné";
		play.style.width = "100px";
		reinitialiser();
		init_inc();
	}
	else // Changement de level
	{
		balleVitesse += 0.15;
		continuer();
	}
	play.style.visibility = "visible";
}

function rejouer()
{
	nb_vies--;
	if(nb_vies == 0)
	{
		var play = document.getElementsByClassName("jouer")[0];
		play.innerHTML = "Rejouer";
		play.style.width = "105px";
		play.style.visibility = "visible";
		reinitialiser();
		init_inc();
	}
	else
	{
		reinitialiser();
		continuer();
		if(nb_vies == 1)
			document.getElementsByClassName("vies")[0].getElementsByTagName("span")[0].innerHTML = nb_vies + " vie";
		else
			document.getElementsByClassName("vies")[0].getElementsByTagName("span")[0].innerHTML = nb_vies + " vies";
	}
}

function continuer()
{
	var play = document.getElementsByClassName("jouer")[0];
	tabBriques = tabLevel[level-1];
	nb_cases = tabCases[level-1];
	play.innerHTML = "Continuer";
	play.style.width = "120px";
	play.style.visibility = "visible";
	nb_touchees = 0;
	barreX = canvas.width/2-BARRE_WIDTH/2;
	barreY = canvas.height-BARRE_HEIGHT-5;
	balleX = canvas.width/2;
	balleY = canvas.height-BARRE_HEIGHT-5-BALLE_DIAMETRE;
	dirBalleX = Math.random()*2-1;
	dirBalleY = -1;
	refresh();
}