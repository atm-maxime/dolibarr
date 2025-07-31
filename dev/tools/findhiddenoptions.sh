#!/bin/bash
# This script searches global confs through Dolibarr code
# Then checks if confs are not present in admin folder (if so it's a hidden conf)
# Then checks if the hidden confs are present on Dolibarr wiki

# Syntax
if [ "x$1" == "x" ]
then
	echo "Search for all hidden options for given folder"
	echo "Usage: ${0##*/} /path/to/dolibarr/htdocs/[foldertosearch/]"
	exit
fi

# Search for all hidden options
prefix="conf->global->"
folder="$1"
folderadmin=$folder"../*/admin/"
setupotherurl="https://wiki.dolibarr.org/index.php/Setup_Other"

echo "---------------------------------------------------------------------"
echo "1. Searching hidden conf in Dolibarr"

# Get all global conf used in Dolibarr core code
echo " > grep -Ron --exclude-dir=admin --exclude-dir=custom \"$prefix[Aa-Zz_]*\" $folder"
lines=$(grep -Ron --exclude-dir=admin --exclude-dir=custom "$prefix[Aa-Zz_]*" $folder)

for line in $lines
do
    # Clean line to get only the conf name
    conf=${line##*$prefix}

    # Same conf can appear several times, we only store it once
    if [[ " ${all[@]} " =~ " $conf " ]]
    then continue
    else
        all+=("$conf")
    fi

    # Search if conf is used in an admin page, i.e. is not hidden
    found=$(grep -R $conf $folderadmin)

    if [ "$found" = "" ]
    then
        hidden+=("$conf")
    fi

    if [ "$conf" = "" ]
    then
        echo $line
    fi
done

#echo "---- Found ${#hidden[@]}"
#for hid in "${hidden[@]}"
#do
#    echo "$hid"
#done
#echo "---------------------------------------------------------------------"

echo "---------------------------------------------------------------------"
echo "2. Searching hidden conf documented on Dolibarr wiki"

# Get all hidden options documented on the wiki
echo " > wget $setupotherurl -q -O -"
wikicontent=$(wget $setupotherurl -q -O -)
lines=$(echo $wikicontent | grep -o "<li>[Aa-Zz_]*")

for line in $lines
do
    # Clean line to get only the conf name
    conf=${line##*<li>}
    documented+=("$conf")
done

#echo "---- Found ${#documented[@]}"
#for doc in "${documented[@]}"
#do
#    echo "$doc"
#done
#echo "---------------------------------------------------------------------"

echo "---------------------------------------------------------------------"
echo "3. Comparing lists"

hiddendeep=("${hidden[@]}")
for i in "${documented[@]}"; do
    hiddendeep=(${hiddendeep[@]//$i})
done

#echo "---- Found ${#hiddendeep[@]}"
#for deep in "${hiddendeep[@]}"
#do
#    echo "$deep"
#done
#echo "---------------------------------------------------------------------"


#echo "---------------------------------------------------------------------"
#echo "4. List of documented hidden conf not present in Dolibarr anymore"

#deprecated=("${documented[@]}")
#for i in "${hidden[@]}"; do
#    deprecated=(${deprecated[@]//*$i*})
#done

#echo "---- Found ${#deprecated[@]}"
#for deprec in "${deprecated[@]}"
#do
#    echo "$deprec"
#done
#echo "---------------------------------------------------------------------"

echo ""
echo "-------------------------------RESULTS-------------------------------"
echo " > ${#all[@]} global conf found in $folder"
echo " > ${#hidden[@]} not present in $folderadmin"
echo " > ${#hiddendeep[@]} not documented on wiki page"
echo "---------------------------------LIST--------------------------------"
for hid in "${hiddendeep[@]}"
do
    echo "$hid"
done
echo "---------------------------------------------------------------------"
exit
