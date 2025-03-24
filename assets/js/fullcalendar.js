import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let modal = new bootstrap.Modal(document.getElementById('eventModal'));
    let form = document.getElementById('eventForm');
    let detailModal = new bootstrap.Modal(document.getElementById('eventDetailModal'));

    if (calendarEl && !calendarEl.dataset.initialized) {
        console.log("✅ Initialisation du calendrier");

        calendarEl.dataset.initialized = "true";

        let calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
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

                // Détecter la vue actuelle
                let viewType = calendar.view.type;

                if (viewType === 'dayGridMonth') {
                    // Si la vue est sur le mois, vider les champs de temps
                    document.getElementById('eventStart').value = '';
                    document.getElementById('eventEnd').value = '';
                }

                // Ouvre le modal
                modal.show();
            },

            select: function (info) {
                console.log("📅 Plage de dates cliquée :", info.startStr, "à", info.endStr);

                // Sauvegarde la plage de dates sélectionnée
                form.dataset.selectedDate = info.startStr;
                let selectedStartDate = new Date(info.startStr);
                let selectedEndDate = new Date(info.endStr);

                // Vérifier si le clic est sur "all-day"
                let isAllDay = info.allDay;

                if (isAllDay) {
                    // Sélectionner l'option "Toute la journée" et désactiver les champs de temps
                    document.getElementById('allDayEvent').checked = true;
                    document.getElementById('eventStart').value = '';
                    document.getElementById('eventEnd').value = '';
                    document.getElementById('eventStart').disabled = true;
                    document.getElementById('eventEnd').disabled = true;
                } else {
                    // Remplir les champs de date et d'heure dans le modal
                    document.getElementById('allDayEvent').checked = false;
                    document.getElementById('eventStart').disabled = false;
                    document.getElementById('eventEnd').disabled = false;
                    document.getElementById('eventStart').value = formatTime(selectedStartDate);
                    document.getElementById('eventEnd').value = formatTime(selectedEndDate);
                }

                // Ouvre le modal
                modal.show();
            },

            eventClick: function (info) {
                console.log("📅 Événement cliqué :", info.event);
            
                // Formater la date et l'heure
                let eventDate = new Date(info.event.start);
                let startDate = formatDate(eventDate);
                let startTime = formatTime(eventDate);
                let endTime = info.event.end ? formatTime(new Date(info.event.end)) : 'Non spécifié';
            
                // Afficher les détails de l'événement dans le modal
                document.getElementById('detailTitle').innerText = info.event.title;
                document.getElementById('detailDate').innerText = startDate;
                document.getElementById('detailStart').innerText = startTime;
                document.getElementById('detailEnd').innerText = endTime;
                document.getElementById('deleteEvent').dataset.eventId = info.event.id;
                document.getElementById('idEvent').innerText = info.event.id;
            
                // Mettre à jour dynamiquement le lien dans le modal
                let link = document.getElementById("revisionLink");
                if (link) {
                    let currentUrl = link.href; // URL de base générée par Symfony avec id=0
                    link.href = currentUrl.replace("/0", "/" + info.event.id); // Remplace 0 par l'ID réel
                }
            
                // Ouvre le modal de détails
                detailModal.show();
            },
            
        });

        calendar.render();

        var deleteEventBtn = document.getElementById('deleteEvent');
        deleteEventBtn.addEventListener('click', function() {
            var eventId = deleteEventBtn.dataset.eventId;
            fetch(`/api/delete-event/${eventId}`, {
                method: 'DELETE',
                headers: {'Content-Type': 'application/json'}
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error); });
                    }
                    return response.json();
                })
                .then((result) => {
                    console.log(result.status);

                    // Supprimer l'événement du calendrier
                    var event = calendar.getEventById(eventId);
                    if (event) {
                        event.remove();
                    }

                    alert("✅ Événement supprimé !");
                    detailModal.hide(); // Ferme le modal après suppression
                })
                .catch(error => console.error('❌ Erreur lors de la suppression de l\'événement:', error));
        });

        // Écouteur pour la case "Toute la journée"
        document.getElementById('allDayEvent').addEventListener('change', function() {
            let startTimeInput = document.getElementById('eventStart');
            let endTimeInput = document.getElementById('eventEnd');
            if (this.checked) {
                startTimeInput.required = false;
                endTimeInput.required = false;
                startTimeInput.value = '';
                endTimeInput.value = '';
                startTimeInput.disabled = true;
                endTimeInput.disabled = true;
            } else {
                startTimeInput.required = true;
                endTimeInput.required = true;
                startTimeInput.disabled = false;
                endTimeInput.disabled = false;
            }
        });

        // Gestion de l'ajout d'événement via le formulaire
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            let titre = document.getElementById('eventTitre').value.trim();
            let startTimeInput = document.getElementById('eventStart');
            let endTimeInput = document.getElementById('eventEnd');
            let selectedDate = form.dataset.selectedDate;
            let allDay = document.getElementById('allDayEvent').checked;

            if (!titre) {
                alert("❌ Remplissez le titre !");
                return;
            }

            let startDateTime, endDateTime;
            if (allDay) {
                startDateTime = selectedDate;
                endDateTime = selectedDate;
            } else {
                let startTime = startTimeInput.value;
                let endTime = endTimeInput.value;

                if (!startTime || !endTime) {
                    alert("❌ Remplissez les heures de début et de fin !");
                    return;
                }

                startDateTime = selectedDate.includes('T') ? selectedDate : `${selectedDate}T${startTime}:00`;
                endDateTime = selectedDate.includes('T') ? selectedDate : `${selectedDate}T${endTime}:00`;
            }

            // Remove timezone information if present
            startDateTime = startDateTime.replace(/(\+|-)\d{2}:\d{2}/, '');
            endDateTime = endDateTime.replace(/(\+|-)\d{2}:\d{2}/, '');

            fetch('/api/add-event', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    titre: titre,
                    start: startDateTime,
                    end: endDateTime,
                    allDay: allDay
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
                        end: endDateTime,
                        allDay: allDay
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

        function formatDate(date) {
            return date.toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatTime(date) {
            return date.toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
});