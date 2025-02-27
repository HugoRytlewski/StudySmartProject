import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let modal = new bootstrap.Modal(document.getElementById('eventModal'));
    let form = document.getElementById('eventForm');

    if (calendarEl && !calendarEl.dataset.initialized) {
        console.log("✅ Initialisation du calendrier");

        calendarEl.dataset.initialized = "true";

        let calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            selectable: true,
            editable: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'fr',
            buttonText: {
                today: 'Aujourd\'hui',
                month: 'Mois',
                week: 'Semaine',
                day: 'Jour',
                list: 'Liste',
            },
            events: '/api/events', // Charger les événements depuis l'API

            dateClick: function (info) {
                console.log("📅 Date cliquée :", info.dateStr);

                // Sauvegarde la date sélectionnée
                form.dataset.selectedDate = info.dateStr;

                // Ouvre le modal
                modal.show();
            }
        });

        calendar.render();

        // Gestion de l'ajout d'événement via le formulaire
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let titre = document.getElementById('eventTitre').value.trim();
            let startTime = document.getElementById('eventStart').value;
            let endTime = document.getElementById('eventEnd').value;
            let selectedDate = form.dataset.selectedDate;

            if (!titre || !startTime || !endTime) {
                alert("❌ Remplissez tous les champs !");
                return;
            }

            let startDateTime = `${selectedDate}T${startTime}:00`;
            let endDateTime = `${selectedDate}T${endTime}:00`;

            fetch('/api/add-event', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    titre: titre,
                    start: startDateTime,
                    end: endDateTime
                }),
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error); });
                    }
                    return response.json();
                })
                .then((event) => {
                    calendar.addEvent({
                        id: event.id,
                        title: titre,
                        start: startDateTime,
                        end: endDateTime
                    });

                    alert("✅ Événement ajouté !");
                    modal.hide(); // Ferme le modal après validation
                    form.reset(); // Réinitialise le formulaire
                })
                .catch(error => console.error('❌ Erreur:', error));
        });

        var addCalendarBtn = document.getElementById('addCalendarBtn');
        var calendarUrlInput = document.getElementById('calendarUrlInput');
        var validateCalendarBtn = document.getElementById('validateCalendarBtn');

        addCalendarBtn.addEventListener('click', function() {
            calendarUrlInput.style.display = 'inline';
            validateCalendarBtn.style.display = 'inline';
        });

        validateCalendarBtn.addEventListener('click', function() {
            var icsUrl = calendarUrlInput.value;
            console.log('Validation du calendrier avec l\'URL:', icsUrl);
            if (icsUrl) {
                fetch(`/proxy.php?url=${encodeURIComponent(icsUrl)}`)
                    .then(response => response.text())
                    .then(data => {
                        console.log('Données ICS récupérées:', data);
                        var events = parseICS(data); // Fonction pour analyser le fichier ICS
                        console.log('Événements analysés:', events);
                        events.forEach(event => {
                            console.log('Ajout de l\'événement:', event);
                            calendar.addEvent(event);
                        });

                        // Enregistrer les événements ICS dans la base de données
                        fetch('/api/add-ics-events', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({ events: events }),
                        })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => { throw new Error(err.error); });
                                }
                                return response.json();
                            })
                            .then((result) => {
                                console.log(result.status);
                            })
                            .catch(error => console.error('❌ Erreur lors de l\'enregistrement des événements ICS:', error));
                    })
                    .catch(error => console.error('Erreur lors de la récupération du fichier ICS:', error));
                calendarUrlInput.style.display = 'none';
                validateCalendarBtn.style.display = 'none';
            } else {
                console.warn('Aucune URL ICS fournie.');
            }
        });

        function parseICS(data) {
            // Fonction de parsing ICS simple pour extraire les événements
            var events = [];
            var lines = data.split('\n');
            var event = null;

            lines.forEach(line => {
                if (line.startsWith('BEGIN:VEVENT')) {
                    event = {};
                } else if (line.startsWith('END:VEVENT')) {
                    events.push(event);
                    event = null;
                } else if (event) {
                    var [key, value] = line.split(':');
                    if (key && value) {
                        event[key] = value.trim();
                    }
                }
            });

            return events.map(event => ({
                title: event['SUMMARY'],
                start: event['DTSTART'],
                end: event['DTEND']
            }));
        }
    }
});