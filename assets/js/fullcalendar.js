import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', function () {
    let calendarEl = document.getElementById('calendar');
    let modal = new bootstrap.Modal(document.getElementById('eventModal'));
    let form = document.getElementById('eventForm');

    if (calendarEl && !calendarEl.dataset.initialized) {
        console.log("✅ Initialisation du calendrier");

        calendarEl.dataset.initialized = "true";

        let calendar = new Calendar(calendarEl, {
            plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
            initialView: 'dayGridMonth',
            selectable: true,
            editable: true,
            events: '/api/events',

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
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: titre,
                    start: startDateTime,
                    end: endDateTime
                }),
            })
                .then(response => response.text())  // 👈 Change `json()` en `text()`
                .then(data => {
                    console.log("🔍 Réponse brute du serveur :", data); // Voir si c'est une erreur HTML
                    try {
                        let jsonData = JSON.parse(data); // Tenter de parser en JSON
                        console.log("✅ Réponse JSON :", jsonData);
                        if (jsonData.error) {
                            alert("❌ Erreur : " + jsonData.error);
                        } else {
                            calendar.refetchEvents();
                            alert("✅ Événement ajouté !");
                        }
                    } catch (e) {
                        console.error("❌ Erreur de parsing JSON :", e);
                    }
                })
                .catch(error => console.error('❌ Erreur:', error));
        });
    }
});
