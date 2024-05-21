//CLASS: TIMER
function Timer(counterTarget) {
    this.counterTarget = counterTarget;
    this.timerStartValue;
    this.timerCounterRef;
    this.timerCounterRefOutOfTime = false;
    this.start = function(startValue) {
        this.timerStartValue = parseInt(startValue);

        if (isNaN(this.timerStartValue)) {
            this.counterTarget.innerHTML = 'Podaj liczbę';
            return;
        }
        else if (startValue <= 0) {
            this.counterTarget.innerHTML = 'Czas minął';
            return;
        }

        this.stop();

        this.timerCounterRefOutOfTime = false;
        this.counterTarget.innerHTML = this.parseSecondsToDhms(this.timerStartValue);

        if (this.timerCounterRef || this.timerStartValue <= 0) {
            this.stop();
            return;
        }

        this.startTimer();
    }
    this.startTimer = function () {
        var self = this;
        this.timerCounterRef = setInterval(function() {
            if (self.timerStartValue <= 0) {
                this.stop();
                self.counterTarget.innerHTML = 'Czas minął';
                self.timerCounterRefOutOfTime = true;
                return;
            }
            --self.timerStartValue;
            self.counterTarget.innerHTML = self.parseSecondsToDhms(self.timerStartValue);
        }, 1000);
    }
    this.stop = function() {
        clearInterval(this.timerCounterRef);
        this.timerCounterRef = undefined;
    }
    this.parseSecondsToDhms = function (sec) {
        var seconds = Number(sec);
        var d = Math.floor(seconds / (3600 * 24))
        var h = Math.floor((seconds % (3600 * 24)) / 3600)
        var m = Math.floor((seconds % 3600) / 60)
        var s = Math.floor(seconds % 60)

        // num (value, ['zero elementów', 'jeden element', 'dwa elementy'], [true - pominięcie wartości na wyjściu])

        var num = function (value, numerals, wovalue) {
            var t0 = value % 10,
                t1 = value % 100,
                vo = [];
            if (wovalue !== true)
                vo.push(value);
            if (value === 1 && numerals[1])
                vo.push(numerals[1]);
            else if ((value == 0 || (t0 >= 0 && t0 <= 1) || (t0 >= 5 && t0 <= 9) || (t1 > 10 && t1 < 20)) && numerals[0])
                vo.push(numerals[0]);
            else if (((t1 < 10 || t1 > 20) && t0 >= 2 && t0 <= 4) && numerals[2])
                vo.push(numerals[2]);
            return vo.join(' ');
        };

        var dDisplay = d > 0 ? d + (d == 1 ? " dzień " : " dni ") : ""
        var hDisplay = h > 0 ? h + (num(h, [' godzin ', ' godzina ', ' godziny '], true)) : ""
        var mDisplay = m > 0 ? m + (num(m, [ ' minut ', ' minuta ', ' minuty '], true)) : ""
        var sDisplay = s > 0 ? s + (num(s, [' sekund', ' sekunda', ' sekundy'], true)) : ""
        return dDisplay + hDisplay + mDisplay + sDisplay
    }
}

function getSrvTime(returnData) {
    $.ajax({
        type: 'HEAD',
        url: window.location.href,
        complete: function (res) {
            var unix = Math.floor(Date.parse(res.getResponseHeader('date')) / 1000);
            returnData(unix);
        }
    });
}

// COOKIE FUNCTIONS

function enrollmentChangesRedirect() {
	var cookie = getCookie("uid");
					
	if (cookie == null) {
		$(".modal-container").css("display","flex");
            $("#modal-text").text("Wpisz 6-znakowy kod:");
            $("#modal-input-text").css("display", "inline-block").focus()
            .on('input', function () {
                $("#modal-hint").remove();
                if ($(this).val().length === 6) {
                    $(this).css("outlineColor", "#0b0");  //green
                }
                else if ($(this).val().length === 0) {
                    $(this).css("outlineColor", "#000");  //black
                }
                else {
                    $(this).css("outlineColor", "#f00");  //red
                }
            })

            $("#modal-accept").click(function () {
                if ($("#modal-input-text").val().length === 6) {
                    $("#modal-form").submit();
                }
                else {
                    if ($("#modal-hint").length > 0) {
                        $("#modal-hint").remove();
                    $(".modal-form-container").append('<p id="modal-hint">Kod powinien zawierać 6 znaków!</p>');
                    }
                    else {
                        $(".modal-form-container").append('<p id="modal-hint">Kod powinien zawierać 6 znaków!</p>');
                    }
                }
            })
	}
	else {
        if (location.href.indexOf("wyniki") > -1) {
            var cookieValue = getCookieValue('uid');
            location.href = "../?uID=" + cookieValue;
        }
        else {
            var cookieValue = getCookieValue('uid');
            location.href = "?uID=" + cookieValue;
        }
	}
	
}

function setCookie(name, value, exdays, currentUID) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays*864e5));
	let expires = "expires="+ d.toUTCString();
	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	document.cookie = name + "=" + value + ";" + expires + ";path=" + dir;
	
		if (name == 'uid') {
			setCookie('nameSurname', nameSurname, 90);

            $("#cookie-button").attr('onclick', "deleteCookie('uid', '" + currentUID + "');")
            .html('<button class="action-button cookie-actual-btn">Usuń kod z pamięci przeglądarki</button>');

		}
		showModal("Kod został poprawnie zapisany w pamięci przeglądarki (jako plik cookie). Informacje będą przechowywane na Twoim urządzeniu przez maksymalnie 90 dni.")
}

function deleteCookie(name, currentUID) {
	var loc = window.location.pathname;
	var dir = loc.substring(0, loc.lastIndexOf('/'));
	document.cookie = name+'=;expires=Thu, 01 Jan 1970 00:00:00 UTC; path=' + dir;
	
		if (name == 'uid') {
			deleteCookie('nameSurname');

            $("#cookie-button").attr('onclick', "setCookie('uid', '" + currentUID + "', 90, '" + currentUID + "');")
            .html('<button class="action-button cookie-actual-btn">Zapisz kod w pamięci przeglądarki</button>');
		}
		showModal("Informacje zostały trwale usunięte z pamięci przeglądarki.");
}

function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
		if (begin == -1) {
			begin = dc.indexOf(prefix);
			if (begin != 0) return null;
		}
		else
		{
			begin += 2;
			var end = document.cookie.indexOf(";", begin);
				if (end == -1) {
				end = dc.length;
				}
		
		}

	return decodeURI(dc.substring(begin + prefix.length, end));
	
}


function getCookieValue(name) {
	const value = `; ${document.cookie}`;
	const parts = value.split(`; ${name}=`);
	if (parts.length === 2) return parts.pop().split(';').shift();
}

function checkCookieMessage(currentUID) {
	var cookie = getCookie('uid');
	
		if (cookie == null) {

            $("#cookie-button").attr('onclick', "setCookie('uid', '" + currentUID + "', 90, '" + currentUID + "');")
            .html('<button class="action-button cookie-actual-btn">Zapisz kod w pamięci przeglądarki</button>');
		}
		else {

            $("#cookie-button").attr('onclick', "deleteCookie('uid', '" + currentUID + "');")
            .html('<button class="action-button cookie-actual-btn">Usuń kod z pamięci przeglądarki</button>');
		}
}

function checkCookieEnrollmentBeginButton() {
	var enrBeginButtonContainer = document.getElementById('enrollment-begin-button-container');
	var cookieNameSurname = getCookie('nameSurname');
	
		if (cookieNameSurname != null) {
			var cookieNameSurnameValue = getCookieValue('nameSurname');
			enrBeginButtonContainer.innerHTML = 'Witamy ponownie, ' + cookieNameSurnameValue + '!';
		}
}

//MODAL FUNCTIONS

function showModalLocate(msg, locate) {
	$(".modal-container").css("display", "flex");
    $(".modal-text").html(msg);

    $("#modal-accept").click(function () {
        window.location.replace(locate);
    })
    $("#modal-decline").click(function () {
        window.location.replace(locate);
    })
    $("#modal-close").click(function () {
        window.location.replace(locate);
    })   

}

function showModal(msg) {
    $(".modal-container").css("display", "flex");
    $(".modal-text").html(msg);

    $("#modal-accept").click(function () {
        $(".modal-container").hide();
        $("#modal-hint").remove();
    })
}

//AJAX

function changeChoice(cmd) {
    showModal('Łączenie z serwerem...');
    const searchParams = new URLSearchParams(window.location.search);
    var thisUID = searchParams.get('uID');
    $.post('action.php', {cmd: cmd, uID: thisUID}, function (data, status) {
        if (status == 'success') {
            showModalLocate(data, "?uID=" + thisUID);
        }
        else {
            showModalLocate("Błąd łączenia z serwerem.", "?uID=" + thisUID);
        }
    })
}




$(document).ready(() => {

    //onload - MAIN


        //MODAL btns
        $("#modal-decline").click(function () {
            $(".modal-container").hide();
            $("#modal-hint").remove();
        })
        $("#modal-close").click(function () {
            $(".modal-container").hide();
            $("#modal-hint").remove();
        })
        //END MODAL btns

        //onclick NAV 1
        $("#btn-nav-begin").click(function () {
            location.href='?uID=new';
        })

        //onclick NAV 2
        $("#btn-nav-changes").click(function () {
            enrollmentChangesRedirect();
        })

        //onclick NAV 3
        $("#btn-nav-results").click(function () {
            location.href='wyniki';
        })

    //onload - INPUT NS
        $(".next-button").each(function () {
            $(this).css("cursor", "not-allowed").attr("disabled", "disabled").fadeTo(100, 0.33);
        })

        $("#participation-deny").click(function () {
            $("#checkbox-participation-container").hide();
            $(".next-button").css("cursor", "not-allowed").attr("disabled", "disabled").fadeTo(100, 0.33);
        })
        $("#participation-accept").click(function () {
            $("#checkbox-participation-container").show();
            $(".next-button").css("cursor", "pointer").fadeTo(100, 1).removeAttr("disabled");
        })

    //onload - ENR FORM
        $("#btn-do-payment").click(function () {
            $("#payment-details-info").toggle();
        })

        //COOKIES
        if ($("#cookie-button").length) {
            checkCookieMessage(currentUID);
        }

        if ($("#btn-nav-begin").length) {
            checkCookieEnrollmentBeginButton();
        }

        //AJAX  CHANGE CHOICE

        if ($("#btn-toga-declare").length) {
            $("#btn-toga-declare").click(function () {
                changeChoice('toga-declare');
            })
        }

        if ($("#btn-toga-resign").length) {
            $("#btn-toga-resign").click(function () {
                changeChoice('toga-resign');
            })
        }

        if ($("#btn-biret-declare").length) {
            $("#btn-biret-declare").click(function () {
                changeChoice('biret-declare');
            })
        }

        if ($("#btn-biret-resign").length) {
            $("#btn-biret-resign").click(function () {
                changeChoice('biret-resign');
            })
        }
    
    //onload - RESULTS

        //COOKIES
        if ($(".table-header-container").length) {
            var cookie = getCookie("uid");

            if (cookie != null) {
                var cookieNsValue = getCookie("nameSurname");
                //highlight your name_surname if cookie exists
                $("td:nth-child(2)").each(function () {
                    if ($(this).text() === cookieNsValue) {
                        $(this).css("fontWeight", "bold");
                    }
                })
                $("#btn-check-status-results").show();
            }
        }

        //TIMER
        if ($("#remaining-time").length) {
            var absTimer = new Timer(document.getElementById("remaining-time"));
            var closingTime = 1719311400;
            getSrvTime(function (srvTimeCallback) {
                var remainingTime = closingTime - srvTimeCallback;
                absTimer.start(remainingTime);
            })
        }
})