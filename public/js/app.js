


$( document ).ready(function() {

	// Tool tips
    $(function () {
	  $('[data-toggle="tooltip"]').tooltip()
	});

	// Popovers
	$(function () {
	  $('[data-toggle="popover"]').popover()
	});

	$(function () {
	  $('.example-popover').popover({
	    container: 'body'
	  })
	});


	$('.popover-dismiss').popover({
	  trigger: 'focus'
	});


	// Password meter 1 (sign up password)
	$(function() {
		var strength = {
			0: "Bad",
			1: "Poor",
			2: "Weak",
			3: "Good",
			4: "Strong"
		}

		var password = document.getElementById('signup-password');
		var meter = document.getElementById('password-strength-meter');
		var text = document.getElementById('password-strength-text');
		
		if (password != null) {
			password.addEventListener('input', function() {
			  var val = password.value;
			  var result = zxcvbn(val);

			  // Update the password strength meter
			  
			  meter.value = result.score;

			  // Update the text indicator
			  if (val !== "") {
			    text.innerHTML = "Password Strength: " + "<strong>" + strength[result.score] + "</strong>" + ". " + "<span class='feedback'>" + result.feedback.warning + " " + result.feedback.suggestions + "</span>";  
			  } else {
			    text.innerHTML = "";
			  }
			});
		}

	});

// Password meter 2 (change password)
	$(function() {
		var strength = {
			0: "Bad",
			1: "Poor",
			2: "Weak",
			3: "Good",
			4: "Strong"
		}

		password = document.getElementById('password_new');
		meter = document.getElementById('password-strength-meter-new');
		text = document.getElementById('password-strength-text-new');

		if (password != null) {
			password.addEventListener('input', function() {
			  var val = password.value;
			  var result = zxcvbn(val);

			  // Update the password strength meter
			  
			  meter.value = result.score;

			  // Update the text indicator
			  if (val !== "") {
			    text.innerHTML = "Password Strength: " + "<strong>" + strength[result.score] + "</strong>" + ". " + "<span class='feedback'>" + result.feedback.warning + " " + result.feedback.suggestions + "</span>";  
			  } else {
			    text.innerHTML = "";
			  }
			});
		}

	});

 	
 	// Make Person details available or not available for editing
	$("#editPerson").on("click", function(event){
	  
    	if ($(".editable").prop("disabled")) {
        	$(".editable").prop("disabled", false);
        	$("#editPerson").html("Disable Editing");
        	$("#lastName").attr("href", "");
        	
     	} else {
     		$(".editable").prop("disabled", true);
        	$("#editPerson").html("Enable Editing");
        	$("#lastName").removeAttr("href");
     	}

     	if ($("#saveChanges").prop("disabled")) {
     		$("#saveChanges").prop("disabled", false);
     		
     	} else {
     		$("#saveChanges").prop("disabled", true);
     		
     	}

	});

	

	// Toggle find person
	$("#personChooseRadio").on("click", function(event){
		$("#personChoose").prop("disabled", false);
		$("#personDetails").prop("disabled", true);
	});

	$("#personDetailsRadio").on("click", function(event){
		$("#personDetails").prop("disabled", false);
		$("#personChoose").prop("disabled", true);
	});

	// Toggle Alive/Deceased
	$('#stateButton').on("click", function(event){
		if ($('#alive').text() == 'Alive') {
			$('#alive').text("Deceased");
		} else {
			$('#alive').text("Alive");
		}
		
	});

	// Toggle unsure of birth date flag
	$('#unsureDateOfBirthFlag').on("click", function(event) {
		$('#unsureDateOfBirthImage').toggleClass("fa-check");
		$('#unsureDateOfBirthImage').toggleClass("fa-question");
		
		if ($('#unsureDOBFlag').val() == '1') {
			$('#unsureDOBFlag').val('0');
		} else {
			$('#unsureDOBFlag').val('1');
		};
	});

	// Toggle unsure of place of birth flag
	$('#unsurePlaceOfBirthFlag').on("click", function(event) {
		$('#unsurePlaceOfBirthImage').toggleClass("fa-check");
		$('#unsurePlaceOfBirthImage').toggleClass("fa-question");
		
		if ($('#unsurePOBFlag').val() == '1') {
			$('#unsurePOBFlag').val('0');
		} else {
			$('#unsurePOBFlag').val('1');
		};
	});

	// Toggle unsure of date of death flag
	$('#unsureDateOfDeathFlag').on("click", function(event) {
		$('#unsureDateOfDeathImage').toggleClass("fa-check");
		$('#unsureDateOfDeathImage').toggleClass("fa-question");
		
		if ($('#unsureDODFlag').val() == '1') {
			$('#unsureDODFlag').val('0');
		} else {
			$('#unsureDODFlag').val('1');
		};
	});

	// Toggle unsure of place of death flag
	$('#unsurePlaceOfDeathFlag').on("click", function(event) {
		$('#unsurePlaceOfDeathImage').toggleClass("fa-check");
		$('#unsurePlaceOfDeathImage').toggleClass("fa-question");
		
		if ($('#unsurePODFlag').val() == '1') {
			$('#unsurePODFlag').val('0');
		} else {
			$('#unsurePODFlag').val('1');
		};
	});


	// Modal template
	$('#imageUpdateModal').on('show.bs.modal', function (event) {
	  var button = $(event.relatedTarget); // Button that triggered the modal
	  var recipient = button.data('whatever'); // Extract info from data-* attributes
	  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
	  var modal = $(this);
	  modal.find('.modal-title').text('New ' + recipient + "'s" + " details");
	  //modal.find('.modal-body input').val(recipient);
	});

	// Modal for parent
	$('#parentModal').on('show.bs.modal', function (event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var personType = button.data('persontype'); // Extract info from data-* attributes
		  
		var personId = button.data('personid');
		//window.alert(personType);
		
		var val= "{{path_for('simpleFindPerson', {'page' : 'person', 'id' : '" + personId + "', 'who' : '" + personType +"' }) }}";

		// Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
		var modal = $(this);
		
		//Capital initial char for personType
		var initialChar = personType.charAt(0);
		var initialChar = initialChar.toUpperCase();
		var persType = initialChar + personType.substring(1);
		modal.find('.modal-title').text("Select New " + persType);
		 
		//action="{{ path_for('simpleFindPerson', {'page' : 'person', 'personId' : person.id, 'who' : 'personType' }) }}"
		var act = modal.find('form').attr('action');
		act = act.replace('personType', personType); 
		modal.find('form').attr('action', act);
	});

	// Modal for partnership
	$('#partnershipModal').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget); // Button that triggered the modal
		var action = button.data('action'); // Extract info from data-* attributes
	  	var firstName = button.data('firstname');
		var nickname = button.data('nickname');
		var middleName = button.data('middlename');
		var lastName = button.data('lastname');
		var dateOfBirth = button.data('dateofbirth');
		var marriageDate = button.data('marriagedate');
		var divorceDate = button.data('divorcedate');
		var personid = button.data('personid');
	  	var partnerid = button.data('partnerid');
		var loc = button.data('loc');

		var modal = $(this);
		modal.find('.modal-title').text(action);
	  	modal.find('#partnerFirstName').val(firstName);
	  	modal.find('#partnerNickname').val(nickname);
	  	modal.find('#partnerMiddleName').val(middleName);
	  	modal.find('#partnerLastName').val(lastName);
	  	modal.find('#partnerDateOfBirth').val(dateOfBirth);
	  	modal.find('#partnerMarriageDate').val(marriageDate);
	  	modal.find('#partnerDivorceDate').val(divorceDate);  
	});

	


	// Write changed values to database
	$("#updatePerson").on("click", function(event){

	});

	// Discard all editing and revert to original values
	$("#cancelPerson").on("click", function(event){
		$(".editable").each(function() {
			$(this).val($(this).attr('value'));
		});
	});

	// Finish viewing/editing and return to knowledgebase page
	$(".finishPerson").on("click", function(event){
		// if elements not saved, ask member
		console.log("Are you finished? " + $(this).attr('href'));
	});

	// Toggle input death data
	$("#deathRadioNo").on("click", function(event){
		$("#deathData")[0].removeAttribute("hidden");
	});

	$("#deathRadioYes").on("click", function(event){
		$("#deathData")[0].setAttribute("hidden", "hidden");

	});

	// Toggle input natural and or adoptive parent's data
	
	$("#parentRadioYes").on("click", function(event){

		$("#parentData")[0].removeAttribute("hidden");
	});

	$("#parentRadioNo").on("click", function(event){
		$("#parentData")[0].setAttribute("hidden", "hidden");
	});

	// Toggle input adoptive parent's data
	
	$("#adoptiveYes").on("click", function(event){
		$("#adoptiveData")[0].removeAttribute("hidden");
	});

	$("#adoptiveNo").on("click", function(event){
		$("#adoptiveData")[0].setAttribute("hidden", "hidden");
	});

	// Toggle partnership
	$("#partnershipNameRadio").on("click", function(event) {
		$("#partnershipPartnerName")[0].setAttribute("disabled", "disabled");

		$("#partnerFirstName")[0].removeAttribute("disabled");
		$("#partnerMiddleName")[0].removeAttribute("disabled");
		$("#partnerLastName")[0].removeAttribute("disabled");
		$("#partnerGender")[0].removeAttribute("disabled");
		$("#partnerDateOfBirth")[0].removeAttribute("disabled");
		$("#partnerPlaceOfBirth")[0].removeAttribute("disabled");
	});

	$("#partnershipNoRadio").on("click", function(event) {
		$("#partnershipPartnerName")[0].removeAttribute("disabled");
		
		$("#partnerFirstName")[0].setAttribute("disabled", "disabled");
		$("#partnerMiddleName")[0].setAttribute("disabled", "disabled");
		$("#partnerLastName")[0].setAttribute("disabled", "disabled");
		$("#partnerGender")[0].setAttribute("disabled", "disabled");
		$("#partnerDateOfBirth")[0].setAttribute("disabled", "disabled");
		$("#partnerPlaceOfBirth")[0].setAttribute("disabled", "disabled");
	});


	
/*
    $('#tree').jstree({
		'core' : {
		  'data' : {
		    'url' : function (node) {
		      return node.id === '#' ?
		        'ajax_roots.json' :
		        'ajax_children.json';
		    },
		    'data' : function (node) {
		      return { 'id' : node.id };
		    }
		  }
	}});
*/

	// Date picker
	var date_input=$('input[name="date"]'); //our date input has the name "date"
    var container= "body";
    var options={
        format: 'dd/mm/yyyy',
        container: container,
        todayHighlight: true,
        autoclose: true,
        endDate: '2017/05/22'
    };
    date_input.datepicker(options);



	// Function to preview image after validation
	$(function() {
		$("#file").change(function() {
			$("#message").empty(); // To remove the previous error message
			var file = this.files[0];
			var imagefile = file.type;
			var match= ["image/jpeg","image/png","image/jpg"];
			if(!((imagefile==match[0]) || (imagefile==match[1]) || (imagefile==match[2]))) {
				$('#previewing').attr('src','noimage.png');
				$("#message").html("<p id='error'>Please Select A valid Image File</p>"+"<h4>Note</h4>"+"<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
				return false;
			}
			else {
				var reader = new FileReader();
				reader.onload = imageIsLoaded;
				reader.readAsDataURL(this.files[0]);
			}
		});
	});

	function imageIsLoaded(e) {
		$("#file").css("color","green");
		$('#image_preview').css("display", "block");
		$('#previewing').attr('src', e.target.result);
		$('#previewing').attr('width', '250px');
		$('#previewing').attr('height', '230px');
	};

});


