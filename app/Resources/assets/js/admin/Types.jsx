import React, { Component } from 'react'

class Types extends Component {

    constructor(props) {
        super(props)
        this.state = {
            types: [],
            filteredTypes: [],
            typesId: null,
            collectivites: [],
            collectiviteId: '',
            userRoles: '',
            name: '',
            nom: '',
            newNom: '',
            infos: ''
        }
        this.handleChangeName = this.handleChangeName.bind(this)
    }

    componentDidMount() {
        this.getCurrentCollectivite()
        this.getCollectivites()
    }

    getTypes (id) {
        if (id === undefined) id = this.state.collectiviteId
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({types: json, filteredTypes: json, collectiviteId: id}))
            .then(() => {
                if (this.state.name) this.onSearchByNameFieldChange(this.state.name)
            })
    }

    getCollectivites () {

        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({collectivites: json}))

    }

    getCurrentCollectivite () {
        fetch(Routing.generate('sesile_user_userapi_getcurrent'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => {
                this.setState({collectiviteId: json.collectivite.id})
                this.setState({userRoles: json.roles})
                this.getTypes(this.state.collectiviteId)
            })
    }

    postTypes () {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_posttypeclasseur'), {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: this.state.nom,
                collectivites: this.state.collectiviteId,
            }),
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(json => {
                const filteredTypes = this.state.filteredTypes
                filteredTypes.push(json)
                this.setState({filteredTypes, nom: '', infos: 'Enregistrement effectué !'})
            })
    }

    updateType(id, nom) {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_updatetypeclasseur', {id}), {
            method: 'put',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: nom,
                collectivites: this.state.collectiviteId,
            }),
            credentials: 'same-origin'
        })
            .then(response => response.json())
            .then(() => {
                this.setState({infos: 'Enregistrement modifié !'})
            })
    }

    deleteType (typeId) {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_remove', {id: typeId}), { method: 'delete', credentials: 'same-origin'})
            .then(response => response.json())
            .then(() => {
                this.getTypes(this.state.collectiviteId)
                this.setState({infos: 'Enregistrement supprimé !'})
            })
    }

    escapedValue(value) {
        const escapedValue = value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

        if (escapedValue === '') {
            this.setState({types: this.getTypes()})
        }

        return RegExp(escapedValue, 'i')
    }

    onSearchByNameFieldChange(value) {
        this.setState({name:value})
        const regex = this.escapedValue(value)
        if (this.state.types) {
            const filteredTypes = this.state.types.filter(type => regex.test(type.nom))
            this.setState({filteredTypes})
        }

    }

    onSearchByCollectiviteFieldChange(value) {
        this.setState({collectiviteId:value})
        this.getTypes(value)
    }

    handleChangeName(id, value) {
        const filteredTypes = this.state.filteredTypes

        filteredTypes.map(filteredTypes => {
            if (filteredTypes.id === id) {
                filteredTypes.nom = value
            }
        })

        this.setState({filteredTypes})
    }

    render() {
        const filteredTypes = this.state.filteredTypes

        const typesRow = filteredTypes && filteredTypes.map(filteredTypes =>
            <div className="cell medium-12 panel-body grid-x" key={filteredTypes.id}>
                <div className="cell medium-6">
                    <input type="text" value={filteredTypes.nom} onChange={(e) => this.handleChangeName(filteredTypes.id, e.target.value)} />
                </div>
                <div className="cell medium-3">
                    <button className="button primary" onClick={() => this.updateType(filteredTypes.id, filteredTypes.nom)}>enregistrer</button>
                </div>
                <div className="cell medium-3">
                    {
                        (filteredTypes.supprimable) ? <button className="button alert" onClick={() => this.deleteType(filteredTypes.id)}>supprimer</button> : ""
                    }
                </div>
            </div>
        )

        const collectivites = this.state.collectivites
        const collectivitesSelect = collectivites && collectivites.map(collectivite =>
                <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>
        )

        return (
            <div>
                <h4 className="text-center text-bold">Rechercher votre type de classeur</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
                <p className="text-center">{ this.state.infos }</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-6 cell">
                                <label htmlFor="circuit_name_search">Lequel ?</label>
                                <input id="type_name_search"
                                   value={this.state.name}
                                   onChange={(event) => this.onSearchByNameFieldChange(event.target.value)}
                                   placeholder="Entrez le nom du circuit..."
                                   type="text" />
                            </div>
                            {
                                (collectivitesSelect.length > 0) ?
                                    <div className="medium-6 cell">
                                        {
                                            (this.state.userRoles.indexOf("ROLE_SUPER_ADMIN")) ?
                                                <div>
                                                    <label htmlFor="collectivite_name_search">Collectivité ?</label>
                                                    <select id="collectivite_name_search" value={this.state.collectiviteId} onChange={(event) => this.onSearchByCollectiviteFieldChange(event.target.value)}>
                                                        {collectivitesSelect}
                                                    </select>
                                                </div>
                                                : <div>Admin</div>
                                        }
                                    </div>
                                    : <div className="medium-6 cell">
                                        <div className="text-center">
                                            Aucune collectivités...
                                        </div>
                                    </div>
                            }

                        </div>
                    </div>
                    <div className="cell medium-8">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-12">Type de classeurs</div>
                            </div>
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-6">
                                    <input type="text" placeholder="Nouveau type" name="nom" onChange={(e) => this.setState({nom: e.target.value})} value={this.state.nom} />
                                </div>
                                <div className="cell medium-3"></div>
                                <div className="cell medium-3">
                                    <button className="button primary" onClick={() => this.postTypes()}>enregistrer</button>
                                </div>
                            </div>
                            {
                                (typesRow.length > 0) ? typesRow :
                                    <div className="cell medium-12 panel-body">
                                        <div className="text-center">
                                            Aucun types ne correspond à votre recherche...
                                        </div>
                                    </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}


export default Types