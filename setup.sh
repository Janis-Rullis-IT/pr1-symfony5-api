#!/bin/bash
# https://github.com/janis-rullis/shell-scripts

# Do a complete docker cleanup if there is only 1 dockerized project. Can be set - `./setup.sh 1`.
ONLY_PROECT=false;
if [[ -n $1 ]]; then
      ONLY_PROJECT=true;
fi

function init(){
  # TODO: Add some notification about causes when executed multiple times.

	echo "Define error reporting level, file seperator, and init direcotry.";
	set -e	; # set -o xtrace;
	# https://unix.stackexchange.com/a/164548 You can preserve newlines in the .env.
	IFS=$''
	DIR=$PWD;
	ROOT_DIR="$(dirname "${DIR}")";
}

function stopDocker(){
	echo "Stop any running container from this project";
	docker-compose down

	if [[ $ONLY_PROJECT = true ]]; then
		echo "Remove any dangling part."
		echo y | docker network prune
		echo y | docker image prune
		echo y | docker volume prune
	fi
}

function initDb(){
	docker-compose up -d pr1-mysql
}

function readEnvVariables(){
	echo "Reading .env variables...";
	FILE=`cat .env`
	DB_PW=`echo $FILE | grep MYSQL_PASSWORD= | cut -d '=' -f2`;

  # https://superuser.com/questions/1225134/why-does-the-base64-of-a-string-contain-n/1225139
  SECRET=`openssl rand -hex 32  | tr -d \\n`
}

# #5 Dockerize the pr1-symfony4.
function setSymfEnv(){
	echo "Setting up the 'pr1-symfony4' container."
	echo "Go into 'symfony4' direcotry...";
	cd symfony4
	echo "Copying '.env.example' to '.env'...";
	cp .env.example .env

	echo "Fill variables collected from the master '.env'...";

	sed -i -e "s/FILL_DB_PASSWORD/$DB_PW/g" .env  
  sed -i -e "s/FILL_APP_SECRET/\"${SECRET}\"/g" .env  
  
  echo "Setting up the '.env.test'...";
  cp .env .env.test
  sed -i -e "s/APP_ENV=dev/APP_ENV=test/g" .env.test
  sed -i -e "s/pr1?serverVersion/pr1_testing?serverVersion/g" .env.test

  echo "Add .env.test specific values ..."
  echo -e "\nKERNEL_CLASS='App\Kernel'\nSYMFONY_DEPRECATIONS_HELPER=999999" >> .env.test;

	cd $DIR;
	echo "'.env' is ready.";

  echo "Initialize a clean API container first ...";
  docker-compose build --no-cache pr1-symfony4
  docker-compose down
}

init
initDb
stopDocker
readEnvVariables
setSymfEnv
echo "Setup is completed."
echo "Starting the project.."
echo "If this is the first time then it will download and setup Docker containers."
chmod a+x start.sh
./start.sh
