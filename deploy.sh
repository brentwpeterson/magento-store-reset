#deploy witout modma
if [ $# -eq 0 ]; then
    echo "You need to provide you destination path"
    echo "Usage: sh deploy.sh /path/to/webroot/app/"
    exit 1
fi
rsync -avz app/ $1
