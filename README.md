# Battle Balancer

Balancer finds and balances two teams for ["World of Tanks"](http://worldoftanks.ru/). This application 
is written for qualification round of ["UA Web Challenge 2014"](http://uawebchallenge.com/).

## Installation and configuration

### 1.1. Via [Vagrant](https://www.vagrantup.com/): 
    
Clone repository and vagrant up: 

    $ git clone git@github.com:krasun/BattleBalancer.git battle-balancer
    $ cd ./battle-balancer/vagrant && vagrant up && vagrant ssh 
    $ cd /home/vagrant/battle-balancer
    
### 1.2. Via [Composer](https://getcomposer.org/doc/00-intro.md): 

Clone repository and install dependencies:

    $ git clone git@github.com:krasun/BattleBalancer.git battle-balancer
    $ cd ./battle-balancer 
    $ php composer.phar install
    
### 2. Configuration 
    
Copy and edit configuration file: 
    
    $ cp config/parameters.php.dist config/parameters.php
    
**You must edit `api_application_id` parameter to yours.**  
   
### 3. Trying 

Run some simple command to ensure that all is OK:

    $ ./bin/balancer balancer:tank-info
   
You should see, something like this: 
   
    Tanks info
    +--------+-------+-----------+--------------+--------------+
    | tankId | level | maxHealth | gunDamageMin | gunDamageMax |
    +--------+-------+-----------+--------------+--------------+
    | 1      | 5     | 400       | 83           | 138          |
    | 3073   | 3     | 220       | 35           | 59           |
    | 16657  | 8     | 1100      | 368          | 613          |
    | 4433   | 9     | 1850      | 173          | 288          |
    | 14865  | 9     | 1550      | 180          | 300          |
                               ...                            
    | 11857  | 6     | 280       | 210          | 350          |
    | 8017   | 3     | 170       | 56           | 94           |
    | 3857   | 7     | 850       | 101          | 169          |
    | 8993   | 9     | 1600      | 180          | 300          |
    | 13089  | 10    | 2000      | 563          | 938          |
    | 16161  | 9     | 450       | 938          | 1563         |
    +--------+-------+-----------+--------------+--------------+
    Summary tanks info
    +--------------+-----+-------+
    | Property     | Min | Max   |
    +--------------+-----+-------+
    | tankId       | 1   | 64817 |
    | level        | 1   | 10    |
    | maxHealth    | 70  | 3000  |
    | gunDamageMin | 6   | 1688  |
    | gunDamageMax | 10  | 2813  |
    +--------------+-----+-------+

## Usage

Balance two teams with default battle configuration: 

    $ ./bin/balancer balancer:balance --verbose
   
Example of output: 

    Start balancing battle for 15 players per team with tank level between 4 and 6...
    Loading teams...
    93 teams successfully loaded.
    Loading players...
    Players successfully loaded.
    Loading players tanks...
    Players tanks successfully loaded.
    Loading tanks...
    Tanks successfully loaded.
    Selected teams:
    +-------+---------+---------+------+----------+-----------------------------------------------+
    | Id    | Members | Combats | Wins | Win rate | URL                                           |
    +-------+---------+---------+------+----------+-----------------------------------------------+
    | 92034 | 97      | 678     | 376  | 0.55     | http://worldoftanks.ru/community/clans/92034/ |
    | 86037 | 80      | 368     | 182  | 0.49     | http://worldoftanks.ru/community/clans/86037/ |
    +-------+---------+---------+------+----------+-----------------------------------------------+
    Selected players:
    +-----------+---------+---------------------+---------+-------+--------+----------+----------+--------+-----------------------------------------------------+
    | Player id | Team id | Tank name           | Mastery | Level | Health | Dam. min | Dam. max | Weight | Player URL                                          |
    +-----------+---------+---------------------+---------+-------+--------+----------+----------+--------+-----------------------------------------------------+
    | 7113350   | 92034   | Alecto              | 2       | 4     | 270    | 56       | 94       | 0.065  | http://worldoftanks.ru/community/accounts/7113350/  |
    | 22511554  | 92034   | Nashorn             | 2       | 6     | 600    | 101      | 169      | 0.127  | http://worldoftanks.ru/community/accounts/22511554/ |
    | 327838    | 92034   | M10 Wolverine       | 0       | 5     | 340    | 83       | 138      | 0.06   | http://worldoftanks.ru/community/accounts/327838/   |
    | 892587    | 92034   | СУ-85               | 1       | 5     | 350    | 86       | 144      | 0.074  | http://worldoftanks.ru/community/accounts/892587/   |
    | 3629740   | 92034   | Pz.Kpfw. IV Ausf. H | 4       | 5     | 440    | 83       | 138      | 0.119  | http://worldoftanks.ru/community/accounts/3629740/  |
    | 3861170   | 92034   | M3 Lee              | 2       | 4     | 310    | 83       | 138      | 0.077  | http://worldoftanks.ru/community/accounts/3861170/  |
    | 18950330  | 92034   | ARL V39             | 3       | 6     | 610    | 83       | 138      | 0.135  | http://worldoftanks.ru/community/accounts/18950330/ |
    | 8110785   | 92034   | Crusader            | 2       | 5     | 410    | 38       | 63       | 0.083  | http://worldoftanks.ru/community/accounts/8110785/  |
    | 276162    | 92034   | Type 3 Chi-Nu       | 0       | 5     | 440    | 56       | 94       | 0.07   | http://worldoftanks.ru/community/accounts/276162/   |
    | 12484804  | 92034   | R106 KV85           | 4       | 6     | 820    | 120      | 200      | 0.188  | http://worldoftanks.ru/community/accounts/12484804/ |
    | 4795590   | 92034   | КВ-1                | 4       | 5     | 590    | 83       | 138      | 0.143  | http://worldoftanks.ru/community/accounts/4795590/  |
    | 2156244   | 92034   | Pz.Kpfw. III/IV     | 2       | 5     | 380    | 83       | 138      | 0.088  | http://worldoftanks.ru/community/accounts/2156244/  |
    | 245489    | 92034   | А-20                | 0       | 4     | 310    | 35       | 59       | 0.045  | http://worldoftanks.ru/community/accounts/245489/   |
    | 14807024  | 92034   | Т-50                | 0       | 4     | 360    | 35       | 59       | 0.053  | http://worldoftanks.ru/community/accounts/14807024/ |
    | 5207545   | 92034   | Crusader            | 0       | 5     | 410    | 38       | 63       | 0.061  | http://worldoftanks.ru/community/accounts/5207545/  |
    | 3686050   | 86037   | M36 Jackson         | 2       | 6     | 560    | 86       | 144      | 0.117  | http://worldoftanks.ru/community/accounts/3686050/  |
    | 2662307   | 86037   | BDR G1 B            | 0       | 5     | 600    | 83       | 138      | 0.101  | http://worldoftanks.ru/community/accounts/2662307/  |
    | 12024228  | 86037   | СУ-85Б              | 4       | 4     | 260    | 83       | 138      | 0.091  | http://worldoftanks.ru/community/accounts/12024228/ |
    | 5186598   | 86037   | M18 Hellcat         | 4       | 6     | 550    | 83       | 138      | 0.137  | http://worldoftanks.ru/community/accounts/5186598/  |
    | 8222631   | 86037   | Pz.Kpfw. III        | 2       | 4     | 310    | 27       | 45       | 0.064  | http://worldoftanks.ru/community/accounts/8222631/  |
    | 12893225  | 86037   | СУ-100              | 4       | 6     | 580    | 120      | 200      | 0.15   | http://worldoftanks.ru/community/accounts/12893225/ |
    | 5544618   | 86037   | BDR G1 B            | 2       | 5     | 600    | 83       | 138      | 0.123  | http://worldoftanks.ru/community/accounts/5544618/  |
    | 5377067   | 86037   | AT 2                | 4       | 5     | 450    | 56       | 94       | 0.115  | http://worldoftanks.ru/community/accounts/5377067/  |
    | 8359724   | 86037   | AMX 12 t            | 3       | 6     | 600    | 83       | 138      | 0.134  | http://worldoftanks.ru/community/accounts/8359724/  |
    | 6015917   | 86037   | Type 58             | 0       | 6     | 720    | 120      | 200      | 0.129  | http://worldoftanks.ru/community/accounts/6015917/  |
    | 1802542   | 86037   | T1 Heavy Tank       | 0       | 5     | 600    | 83       | 138      | 0.101  | http://worldoftanks.ru/community/accounts/1802542/  |
    | 1061085   | 86037   | А-20                | 3       | 4     | 310    | 35       | 59       | 0.077  | http://worldoftanks.ru/community/accounts/1061085/  |
    | 5776824   | 86037   | Alecto              | 2       | 4     | 270    | 56       | 94       | 0.065  | http://worldoftanks.ru/community/accounts/5776824/  |
    | 784698    | 86037   | M4A3E8 Sherman      | 2       | 6     | 720    | 83       | 138      | 0.142  | http://worldoftanks.ru/community/accounts/784698/   |
    | 17789628  | 86037   | Т-34-85             | 4       | 6     | 670    | 86       | 144      | 0.156  | http://worldoftanks.ru/community/accounts/17789628/ |
    +-----------+---------+---------------------+---------+-------+--------+----------+----------+--------+-----------------------------------------------------+
    Tech. information:
    +-----------------+-----------------+------------------+
    | Peek mem. usage | Run time (sec.) | API request num. |
    +-----------------+-----------------+------------------+
    | 28Mb            | 24.916818857193 | 7                |
    +-----------------+-----------------+------------------+
    Good luck!

You can specify minimum and maximum tank levels: 

    $ ./bin/balancer balancer:balance --min-tank-level=1 --max-tank-level=10 --verbose
   
## Internals

You can read about about core decisions and thoughts: 

* [Core decisions and thoughts (Russian)](https://github.com/krasun/BattleBalancer/blob/master/doc/ru/core-decisions-and-thoughts.md)
