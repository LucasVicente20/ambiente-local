find . -name "*.php" > arquivos.txt
for i in `cat arquivos.txt`
do
awk '{if(NR==1)sub(/^\xef\xbb\xbf/,"");print}' $i> $i.afo
done

find . -name "*.afo" > arquivos.txt
for i in `cat arquivos.txt`
do
a=`echo $i | cut -f2 -d"."`
echo .$a
 cp $i .$a.php
 rm $i
done


