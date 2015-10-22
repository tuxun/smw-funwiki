function creer_briques()
{
	var NOMBRE_NIVEAUX = 5;
	tabCouleurs = new Array();
	tabCases = new Array();
	
	for(x = 0 ; x < NOMBRE_NIVEAUX ; x++)
	{
		var nb = 0;
		tabBriques = new Array(NB_LIGNES);
		tabCouleurs[tabLevel.length] = new Array(NB_LIGNES);
		tabBriques = new Array(NB_LIGNES);
		
		for(var i = 0 ; i < NB_LIGNES ; i++)
		{
			tabBriques[i] = new Array(NB_BRIQUES_PAR_LIGNE);
			tabCouleurs[tabLevel.length][i] = "black";
			
			for(var j = 0 ; j < NB_BRIQUES_PAR_LIGNE ; j++)
			{
				if(afficher(tabLevel.length, i, j))
				{
					tabBriques[i][j] = 1;
					nb++;
				}
				else
					tabBriques[i][j] = 0;
			}
		}	
		tabLevel.push(tabBriques);
		tabCases.push(nb);
	}
}

function afficher(index, i, j)
{
	if((i == 0 && j >= 5 && j <= 7
			|| i == 1 && j >= 4 && j <= 8
			|| i == 2 && j >= 3 && j <= 9
			|| i == 3 && j >= 2 && j <= 10 && j != 4 && j != 8
			|| i == 4 && j >= 2 && j <= 10
			|| i == 5 && (j == 4 || j == 8)
			|| i == 6 && ((j >= 5 && j <= 7) || (j == 3 || j == 9))
			|| i == 7 && (j == 2 || j == 4 || j == 8 || j == 10))
			&& index == 0)
		return true;
	else if((i == 0 && (j == 3 || j == 9)
			|| i == 1 && (j == 4 || j == 8)
			|| i == 2 && j >= 3 && j <= 9
			|| i == 3 && j >= 2 && j <= 10 && j != 4 && j != 8
			|| i == 4 && j >= 1 && j <= 11
			|| i == 5 && j >= 1 && j <= 11 && j != 2 && j != 10
			|| i == 6 && (j == 1 || j == 3 || j == 9 || j == 11)
			|| i == 7 && j >= 4 && j <= 8 && j != 6)
			&& index == 1)
		return true;
	else if((i == 0 && (j == 3 || j == 9)
			|| i == 1 && (j == 1 || j == 4 || j == 8 || j == 11)
			|| i == 2 && (j >= 3 && j <= 9 || j == 1 || j == 11)
			|| i == 3 && j >= 1 && j <= 11 && j != 4 && j != 8
			|| i == 4 && j >= 1 && j <= 11
			|| i == 5 && j >= 2 && j <= 10
			|| i == 6 && (j == 3 || j == 9)
			|| i == 7 && (j == 2 || j == 10))
			&& index == 2)
		return true;
	else if((i == 0 && j >= 5 && j <= 7
			|| i == 1 && j >= 2 && j <= 10
			|| i == 2 && j >= 1 && j <= 11
			|| i == 3 && j >= 1 && j <= 11 && j != 4 && j != 5 && j != 7 && j != 8
			|| i == 4 && j >= 1 && j <= 11
			|| i == 5 && (j == 4 || j == 5 || j == 7 || j == 8)
			|| i == 6 && (j == 3 || j == 4 || j == 6 || j == 8 || j == 9)
			|| i == 7 && (j == 1 || j == 2 || j == 10 || j == 11))
			&& index == 3)
		return true;
	else if((i == 0 && j >= 5 && j <= 7
			|| i == 1 && j >= 3 && j <= 9
			|| i == 2 && j >= 2 && j <= 10
			|| i == 3 && j >= 1 && j <= 11 && j != 4 && j != 6 && j != 8
			|| i == 4 && j >= 0 && j <= 12
			|| i == 5 && ((j >= 1 && j <= 3) || j == 6 || (j >= 9 && j <= 11))
			|| i == 7)
			&& index == 4)
		return true;
	else
		return false;
}

function reinitialiser()
{
	var tab = tabLevel;
	tabLevel = new Array();
	
	for(var k = 0 ; k < tab.length ; k++)
	{
		var tabB = new Array(NB_LIGNES);
		for(var i = 0 ; i < tab[k].length ; i++)
		{
			tabB[i] = new Array(NB_BRIQUES_PAR_LIGNE);
			for(var j = 0 ; j < tab[k][i].length ; j++)
			{
				if(afficher(k, i, j))
					tabB[i][j] = 1;
				else
					tabB[i][j] = 0;
			}
		}
		tabLevel.push(tabB);
	}
}