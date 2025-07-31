#!/bin/bash

# Fonction pour afficher l'aide
usage() {
    echo "Utilisation : $0 <chemin_repertoire_principal> [nom_sous_repertoire_admin]"
    echo "Exemple : $0 /chemin/vers/mon/projet admin"
    echo "         $0 /home/user/code"
    echo "Note : Le nom du sous-répertoire admin (par défaut 'admin') sera recherché à tous les niveaux."
    exit 1
}

# Vérification du nombre d'arguments
if [ "$#" -lt 1 ]; then
    usage
fi

MAIN_DIR="$1"
ADMIN_SUBDIR_NAME="${2:-admin}" # Nom du sous-répertoire admin (ex: 'admin')

# Vérification si le répertoire principal existe
if [ ! -d "$MAIN_DIR" ]; then
    echo "Erreur : Le répertoire principal '$MAIN_DIR' n'existe pas."
    exit 1
fi

# --- Configuration de la Regex pour grep ---
VARIABLE_REGEX="getDolGlobalString\('([a-zA-Z0-9_]+)'\)"
# --- Fin de la Configuration ---

echo "Recherche des variables dans les fichiers du répertoire principal : '$MAIN_DIR'"
echo "Recherche des variables dans tous les sous-répertoires nommés '$ADMIN_SUBDIR_NAME'"
echo "Pattern de recherche des variables (regex) : '$VARIABLE_REGEX'"
echo "---"

# Fichiers temporaires pour stocker les variables
TEMP_MAIN_VARS=$(mktemp)
TEMP_ADMIN_VARS=$(mktemp)

# Assurez-vous que les fichiers temporaires sont supprimés à la sortie du script
trap 'rm -f $TEMP_MAIN_VARS $TEMP_ADMIN_VARS' EXIT

# 1. Lister toutes les variables dans les fichiers du répertoire principal
# Exclure les fichiers se trouvant sous N'IMPORTE QUEL répertoire dont le nom est ADMIN_SUBDIR_NAME
echo "Collecte des variables du répertoire principal (hors sous-dossiers '$ADMIN_SUBDIR_NAME')..."
find "$MAIN_DIR" -type f -print0 | while IFS= read -r -d $'\0' file; do
    # Vérifier si le chemin du fichier contient le sous-répertoire admin
    # On utilise un chemin relatif par rapport à MAIN_DIR pour la comparaison
    relative_path="${file#"$MAIN_DIR"/}"
    if [[ "$relative_path" == */"$ADMIN_SUBDIR_NAME"/* ]]; then
        continue # Ignorer ce fichier s'il est dans un dossier admin
    fi

    # Si le fichier n'est pas dans un dossier admin, chercher la variable
    grep -oP "$VARIABLE_REGEX" "$file" | \
        sed -E "s/^getDolGlobalString\('((.*))'\)$/\1/" >> "$TEMP_MAIN_VARS"
done
sort -u -o "$TEMP_MAIN_VARS" "$TEMP_MAIN_VARS" # Trie et dédoublonne sur place

# 2. Lister les variables dans tous les sous-répertoires "admin"
echo "Collecte des variables de tous les sous-dossiers '$ADMIN_SUBDIR_NAME'..."
found_admin_dirs=false
while IFS= read -r -d $'\0' admin_dir; do
    found_admin_dirs=true
    find "$admin_dir" -type f -print0 | \
        xargs -0 grep -oP "$VARIABLE_REGEX" | \
        sed -E "s/^getDolGlobalString\('((.*))'\)$/\1/" >> "$TEMP_ADMIN_VARS"
done < <(find "$MAIN_DIR" -type d -name "$ADMIN_SUBDIR_NAME" -print0)

# Trie et dédoublonne le fichier des variables admin
sort -u -o "$TEMP_ADMIN_VARS" "$TEMP_ADMIN_VARS"

if ! $found_admin_dirs && [ ! -s "$TEMP_ADMIN_VARS" ]; then # Vérifie si aucun dossier admin n'a été trouvé ET si le fichier est vide
    echo "Aucun sous-répertoire nommé '$ADMIN_SUBDIR_NAME' n'a été trouvé sous '$MAIN_DIR'."
fi

echo "Variables trouvées dans le répertoire principal (hors admin) :"
cat "$TEMP_MAIN_VARS"
echo "---"

echo "Variables trouvées dans les sous-répertoires admin :"
cat "$TEMP_ADMIN_VARS"
echo "---"

# 3. Comparer les deux listes et afficher les variables non présentes dans "admin"
echo "Variables présentes dans le répertoire principal mais ABSENTES des sous-répertoires admin :"
echo "--------------------------------------------------------------------------"
comm -23 "$TEMP_MAIN_VARS" "$TEMP_ADMIN_VARS"

echo "--------------------------------------------------------------------------"
echo "Script terminé."
