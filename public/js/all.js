/* Rafraîchir chaque page auto */
document.addEventListener("turbo:load", function () {
    if (!sessionStorage.getItem("pageReloaded")) {
        sessionStorage.setItem("pageReloaded", "true");
        location.reload();
    } else {
        sessionStorage.removeItem("pageReloaded"); // Reset pour la prochaine navigation
    }
});




/************** NAVBAR ******************/ 



// Sélection de la navbar
const navbar = document.querySelector('nav');

// Fonction pour détecter le scroll
window.addEventListener('scroll', function() {
    if (window.scrollY > 80) {
        // Ajoute la classe 'scrolled' lorsque l'utilisateur a scrollé de plus de 50px
        navbar.classList.add('scrolled');
    } else {
        // Retire la classe 'scrolled' si l'utilisateur remonte en haut de la page
        navbar.classList.remove('scrolled');
    }
});


    /******* Crouselle de la page project(seul)  *********/

    let currentIndex = 0;

function moveCarousel(direction) {
    const carousel = document.getElementById('carousel');
    const items = document.querySelectorAll('.carousel-item');

    if (items.length === 0) return; // Empêche une erreur si le carrousel est vide

    currentIndex = (currentIndex + direction + items.length) % items.length;
    const offset = -currentIndex * 100;

    carousel.style.transform = `translateX(${offset}%)`;
}




    

    /*Pour choisir plusieur vidéo et image dans la création de project  */
    
    
    document.addEventListener('DOMContentLoaded', function () {
        
        function addFileInput(containerId, name, accept) {
            let container = document.getElementById(containerId);
            let input = document.createElement('input');
            input.type = 'file';
            input.name = name;
            input.accept = accept;
            input.multiple = true; // Permet de sélectionner plusieurs fichiers à la fois
            container.appendChild(input);
        }

        document.getElementById('add-image').addEventListener('click', function () {
            addFileInput('image-container', 'images[]', 'image/*');
        });

        document.getElementById('add-video').addEventListener('click', function () {
            addFileInput('video-container', 'videos[]', 'video/*');
        });

        document.getElementById('new-project-form').addEventListener('submit', function(event) {
            let imageFiles = document.querySelectorAll('input[name="images[]"]');
            let videoFiles = document.querySelectorAll('input[name="videos[]"]');
            let maxSize = 100 * 1024 * 1024; // 100MB
            let maxFiles = 2; // On limite à 5 fichiers max

            function checkFiles(files, type) {
                let totalFiles = 0;
                for (let input of files) {
                    for (let file of input.files) {
                        if (file.size > maxSize) {
                            alert(`Le fichier ${file.name} est trop volumineux (max 100MB)`);
                            event.preventDefault();
                            return false;
                        }
                        totalFiles++;
                    }
                }
                if (totalFiles > maxFiles) {
                    alert(`Vous ne pouvez pas envoyer plus de ${maxFiles} ${type}`);
                    event.preventDefault();
                    return false;
                }
                return true;
            }

            if (!checkFiles(imageFiles, 'images') || !checkFiles(videoFiles, 'vidéos')) {
                return;
            }
        });
    });



    /* Pour liker un project */

    $('#like-form').submit(function(e) {
        e.preventDefault();

        // Envoi de la requête AJAX pour ajouter ou retirer un like
        $.post($(this).attr('action'), $(this).serialize(), function(response) {
            // Mise à jour du compteur de likes avec la réponse de l'API
            $('#like-count').text(response.likes);
        });
    });
    


    

    /* Pour partager un projet */
    
    

     // Fonction pour copier le lien
        function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert("Lien copié !");
        }).catch(err => {
            console.error("Erreur lors de la copie", err);
        });
    }
    console.log("✅ all.js chargé !");


    
    /* Js du boutton follow */

    document.addEventListener("DOMContentLoaded", function () {
        
        const followBtn = document.getElementById("follow-btn");
       
    
        if (followBtn) {
            console.log("Bouton trouvé :", followBtn);
            followBtn.addEventListener("click", function () {
                const userId = followBtn.dataset.userId;
    
                fetch(`/follow/${userId}`, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    credentials: "same-origin"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.following !== undefined) {
                        followBtn.textContent = data.following ? "Se désabonner" : "Suivre";
                    } else {
                        console.error("Réponse inattendue :", data);
                    }
                })
                .catch(error => console.error("Erreur:", error));
            });
        }
    });
    
    