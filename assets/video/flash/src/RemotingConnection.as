package 
{
    import flash.net.NetConnection;
    import flash.net.ObjectEncoding;
 
    public class RemotingConnection extends NetConnection
    {
    public function RemotingConnection( sURL:String )
        {
            objectEncoding = ObjectEncoding.AMF0;
            if (sURL) connect( sURL );
        }
        
    }
}