<?php

class RemoteConnection
{
    public static function sendCommand($server, $command)
    {
        require_once(__ROOT__ . '/utils/Rcon.php');
        require(__ROOT__ . '/constants/Constants.php');

        $rhost = explode(":", $rconServers[$server])[0];
        $rport = explode(":", $rconServers[$server])[1];
        $rpassword = 'Qd2CK$zZw&Rs';
        $rtimeout = 3;
        $rcon = new Rcon($rhost, $rport, $rpassword, $rtimeout);
        if (!$rcon->connect()) {
            return false;
        }
        if (!$rcon->sendCommand($command)) {
            $rcon->disconnect();
            return false;
        }
        $rcon->disconnect();
        return true;
    }

    public static function sendDiscordCommand($command)
    {
        require(__ROOT__ . '/constants/Constants.php');

        $PORT = $discordPort; //the port on which we are connecting to the "remote" machine
        $HOST = $discordServer; //the ip of the remote machine (in this case it's the same machine)

        $sock = socket_create(AF_INET, SOCK_STREAM, 0) //Creating a TCP socket
        or die("Hata: Bir sorun oluştu. Hata kodu: 1 (Lütfen yöneticiye iletiniz ve bir süre sonra tekrar deneyiniz.)\n");

        $succ = socket_connect($sock, $HOST, $PORT) //Connecting to to server using that socket
        or die("Hata: Bir sorun oluştu. Hata kodu: 2 (Lütfen yöneticiye iletiniz ve bir süre sonra tekrar deneyiniz.)\n");

        socket_write($sock, $command . "\n", strlen($command) + 1) //Writing the text to the socket
        or die("Hata: Bir sorun oluştu. Hata kodu: 3 (Lütfen yöneticiye iletiniz ve bir süre sonra tekrar deneyiniz.)\n");

        socket_close($sock);
    }
}