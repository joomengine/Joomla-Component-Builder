###BOM###

Joomla.submitbutton = function(task)
{
	if (task == ''){
		return false;
	} else {
		var action = task.split('.');
		if (action[1] == 'cancel' || action[1] == 'close' || document.formvalidator.isValid(document.getElementById("adminForm"))){
			Joomla.submitform(task, document.getElementById("adminForm"));
			return true;
		} else {
			alert(Joomla.Text._('###sview###, some values are not acceptable.','Some values are unacceptable'));
			return false;
		}
	}
}