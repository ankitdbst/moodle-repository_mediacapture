// ActionScript file
import classes.Recorder;

import components.gauge.events.GaugeEvent;

import flash.media.Camera;
import flash.utils.Timer;

import mx.controls.Alert;
import mx.core.Application;


NetConnection.defaultObjectEncoding = flash.net.ObjectEncoding.AMF3;
SharedObject.defaultObjectEncoding  = flash.net.ObjectEncoding.AMF3;

public var nc:NetConnection;
public var ns:NetStream;					//
[Bindable] public var so_chat:SharedObject;
public var camera:Camera;
public var mic:Microphone;
public var nsOutGoing:NetStream;
//public var nsInGoing:NetStream;
public const ROOMMODEL:String="models";
[Bindable] public var myRecorder:Recorder;
public var DEBUG:Boolean=false;
public var recordingTimer:Timer = new Timer( 1000 , 0 );
[Bindable] public var timeLeft:String="";



public function init():void {
	myRecorder = new Recorder();
	
	// get parameters
	if(Application.application.parameters.maxLength!=null) myRecorder.maxLength= Application.application.parameters.maxLength;
	if(Application.application.parameters.fileName!=null) myRecorder.fileName = Application.application.parameters.fileName;
	if(Application.application.parameters.width!=null) myRecorder.width= Application.application.parameters.width;
	if(Application.application.parameters.height!=null) myRecorder.height= Application.application.parameters.height;
	if(Application.application.parameters.server!=null) myRecorder.server= Application.application.parameters.server;
	if(Application.application.parameters.fps!=null) myRecorder.fps= Application.application.parameters.fps;
	if(Application.application.parameters.microRate!=null) myRecorder.microRate= Application.application.parameters.microRate;
	if(Application.application.parameters.showVolume!=null) myRecorder.showVolume= Application.application.parameters.showVolume;
	if(Application.application.parameters.recordingText!=null) myRecorder.recordingText= Application.application.parameters.recordingText;
	if(Application.application.parameters.timeLeftText!=null) myRecorder.timeLeftText= Application.application.parameters.timeLeftText;
	if(Application.application.parameters.timeLeft!=null) myRecorder.timeLeft= Application.application.parameters.timeLeft;
	if(Application.application.parameters.mode!=null) myRecorder.mode= Application.application.parameters.mode;
	if(Application.application.parameters.backToRecorder!=null) myRecorder.backToRecorder= Application.application.parameters.backToRecorder;
	if(Application.application.parameters.backText!=null) myRecorder.backText= Application.application.parameters.backText;
	
	Application.application.width = myRecorder.width;
	Application.application.height = myRecorder.height;

	recordingTimer.addEventListener( "timer" , decrementTimer );

	timeLeft = myRecorder.maxLength.toString();
  	nc=new NetConnection();		
	nc.client=this;		
	nc.addEventListener(NetStatusEvent.NET_STATUS,netStatusHandler);
	nc.connect(myRecorder.server);	

	
	
	if (myRecorder.mode=="player") {
		currentState="player";
	} else {
		currentState="";
	}
	
}

private function recClicked():void { 
	if (rec_btn.selected) {
		recordingTimer.start();
		recordStart();
	}
	if (!rec_btn.selected) {
		recordingTimer.stop();
		recordFinished();
	}
}
private function videoIsComplete():void {
	playPauseBut.selected=true;
	playPause();
}
private function thumbClicked(e:MouseEvent):void {
	videoPlayer.playheadTime = position.value;	
}
public function stopVideo():void {
	var s:String = myRecorder.server+myRecorder.fileName+".flv";
	videoPlayer.source = s;
	videoPlayer.stop();
	playPauseBut.selected = false;
}
private function replay():void {
	rec_btn.selected=false;
	recClicked();
	currentState="player";
	var s:String = myRecorder.server+myRecorder.fileName+".flv";
	videoPlayer.source = s;
	// and start the video !
	playPauseBut.selected=false;
	playPause();
}

private function playPause():void{
	if (playPauseBut.selected) {
		videoPlayer.pause();
	} else {
		videoPlayer.play();
	}
}
private function thumbPressed():void {
	playPauseBut.selected=true;
	videoPlayer.pause();
}	


private function thumbReleased():void {
	videoPlayer.playheadTime = position.value;
	return;
		
	videoPlayer.playheadTime = position.value;	
	if (playPauseBut.selected) {
		videoPlayer.pause();
	} else {
		videoPlayer.play();	
	}
}

 private function formatPositionToolTip(value:Number):String{
	return value.toFixed(2) +" s";
 }
private function handleGaugeEvent( event:GaugeEvent ) : void{	
	videoPlayer.volume = event.value/100;
}
private function rollOut(e:MouseEvent):void {
}
private function rollOver(e:MouseEvent):void {
} 
private function netStatusHandler(event:NetStatusEvent):void {
	switch (event.info.code) {
	case "NetConnection.Connect.Failed":
		Alert.show("ERROR:Could not connect to: "+myRecorder.server);
	break;	
    case "NetConnection.Connect.Success":
    	prepareStreams();
    break;
	default:
		nc.close();
		break;
    }
}
public function recordStart():void {
	nsOutGoing.publish(myRecorder.fileName, "record");
	myRecorder.hasRecorded = true;
}
public function recordFinished():void {
	nsOutGoing.close();
}
private  function decrementTimer( event:TimerEvent ):void {
	var minutes:int;
	var seconds:int;
	myRecorder.timeLeft--;
	minutes = myRecorder.timeLeft / 60;
	seconds = myRecorder.timeLeft % 60;
	if (minutes<10) timeLeft="0"+ minutes+":" else timeLeft=minutes+":";
	if (seconds<10) timeLeft=timeLeft+"0"+ seconds else timeLeft=timeLeft+seconds;

	
	// format to display mm:ss format
	if (myRecorder.timeLeft==0) {
		recordFinished();
	}
}

public function webcamParameters():void {
	Security.showSettings(SecurityPanel.DEFAULT);
}
private function drawMicLevel(evt:TimerEvent):void {
		var ac:int=mic.activityLevel;
		micLevel.setProgress(ac,100);
}

private  function prepareStreams():void {
	nsOutGoing = new NetStream(nc); 
	camera=Camera.getCamera();
	if (camera==null) {
		Alert.show("Webcam not detected !");
	}
	if (camera!=null) {
		if (camera.muted) 	{
			Security.showSettings(SecurityPanel.DEFAULT);
		}
		camera.setMode(myRecorder.width,myRecorder.height,myRecorder.fps);
		myWebcam.attachCamera(camera);
		nsOutGoing.attachCamera(camera);
		myRecorder.cameraDetected=true;
		camera.addEventListener(StatusEvent.STATUS, cameraStatus); 
	}	

	mic=Microphone.getMicrophone(0);
	if (mic!=null) {
        mic.rate=myRecorder.microRate;
        var timer:Timer=new Timer(50);
		timer.addEventListener(TimerEvent.TIMER, drawMicLevel);
		timer.start();
		nsOutGoing.attachAudio(mic);
	}	
	//nsInGoing= new NetStream(nc);
    //nsInGoing.client=this;    
			            
}   
private function cameraStatus(evt:StatusEvent):void {
	switch (evt.code) {
	case "Camera.Muted":
		myRecorder.cameraDetected=false;
		break;
	case "Camera.Unmuted":
    	myRecorder.cameraDetected=true;
	break;
    }
}   
