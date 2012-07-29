package components.gauge.events
{
	import flash.display.InteractiveObject;
	import flash.events.MouseEvent;

	public class GaugeEvent extends MouseEvent
	{
		public function GaugeEvent(currentValue:Number, value:Number, localX:Number=0, localY:Number=0, relatedObject:InteractiveObject=null, ctrlKey:Boolean=false, altKey:Boolean=false, shiftKey:Boolean=false, buttonDown:Boolean=false, delta:int=0.0)
		{
			super("gaugeClick", true, true, localX, localY, relatedObject, ctrlKey, altKey, shiftKey, buttonDown, delta);
			this.currentValue = currentValue;
			this.value = value;
		}
		
		public var currentValue:Number;
		public var value:Number;
		
	}
}