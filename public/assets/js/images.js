let links=document.querySelectorAll("[data-delete]");
//on boucle sur les liens
for (let link of links)
{
    //on met en écouteur d'évenment
    link.addEventListener("click",function(e){
        //on empach la navégation
        e.preventDefault();

        if (confirm("voulez-vous supprimer cette image ?")) {
            //on envois la requete ajax
            fetch(this.getAttribute("href"),{
                method : "DELETE",
                headers : {
                    "X-Requested-With": "XMLHTTPRequest",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({"_token" : this.dataset.token})
            }).then(response => response.json())
            .then(data=>{
                if (data.success) {
                    this.parentElement.remove();
                }else
                alert(data.error); 
            })
        }
    })
    
}