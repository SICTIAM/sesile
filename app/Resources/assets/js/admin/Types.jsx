import React, { Component } from 'react'
import PropTypes from 'prop-types'
import { escapedValue } from '../_utils/Search'

const { object, func } = PropTypes

class Types extends Component {

    constructor(props) {
        super(props)
        this.state = {
            isSuperAdmin: false,
            types: [],
            filteredTypes: [],
            typesId: null,
            collectivites: [],
            currentCollectiviteId: '',
            userRoles: '',
            searchFieldName: '',
            nom: ''
        }
    }

    componentDidMount() {
        const user = this.props.user
        this.fetchTypes(user.collectivite.id)
        this.setState({currentCollectiviteId: user.collectivite.id})
        if(user.roles.includes("ROLE_SUPER_ADMIN")) {
            this.fetchCollectivites()
            this.setState({isSuperAdmin: true})
        }
    }

    fetchTypes = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({types: json, filteredTypes: json}))
            .then(() => {if(this.state.searchFieldName) this.handleChangeSearchByName(this.state.searchFieldName)})
    }

    fetchCollectivites() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({collectivites: json.filter(collectivite => collectivite.active)}))
    }

    createType() {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_posttypeclasseur'), {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: this.state.nom,
                collectivites: this.state.currentCollectiviteId,
            }),
            credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(json => {
                let filteredTypes = this.state.filteredTypes
                filteredTypes = [json, ...filteredTypes]
                this.setState({filteredTypes, nom: ''})
            })
    }

    updateType = (id, nom) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_updatetypeclasseur', {id}), {
            method: 'put',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: nom,
                collectivites: this.state.currentCollectiviteId,
            }),
            credentials: 'same-origin'
            })
            .then(response => response.json())
            .then((json) => {
                const filteredTypes = this.state.filteredTypes
                filteredTypes.map(filteredType => {if (filteredType.id === id) filteredType = json})
                this.setState({filteredTypes})
            })
    }

    removeType = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_remove', {id}), {
            method: 'delete',
            credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(() => this.fetchTypes(this.state.currentCollectiviteId))
    }

    handleChangeSearchByName = (searchFieldName) => {
        this.setState({searchFieldName})
        const regex = escapedValue(searchFieldName, this.state.filteredTypes, this.state.types)
        const filteredTypes = this.state.types.filter(type => regex.test(type.nom))
        this.setState({filteredTypes})
    }

    handleChangeSearchByCollectivite = (currentCollectiviteId) => {
        this.setState({currentCollectiviteId})
        this.fetchTypes(currentCollectiviteId)
    }

    handleChangeNameFields = (id, value) => {
        const filteredTypes = this.state.filteredTypes
        filteredTypes.map(filteredType => {if (filteredType.id === id) filteredType.nom = value})
        this.setState({filteredTypes})
    }

    render() {
        const { filteredTypes, collectivites, isSuperAdmin } = this.state
        const listType = filteredTypes.map(type => <TypeRow key={type.id}
                                                            type={type}
                                                            removeType={this.removeType}
                                                            updateType={this.updateType}
                                                            handleChangeNameFields={this.handleChangeNameFields}/>)
        const listCollectivite = collectivites.map(collectivite =>
                <option value={collectivite.id} key={collectivite.id}>{collectivite.nom}</option>)

        return (
            <div>
                <h4 className="text-center text-bold">Rechercher votre type de classeur</h4>
                <p className="text-center">Puis accéder aux paramétres</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-auto cell">
                                <label htmlFor="circuit_name_search">Lequel ?</label>
                                <input id="type_name_search"
                                   value={this.state.searchFieldName}
                                   onChange={(event) => this.handleChangeSearchByName(event.target.value)}
                                   placeholder="Entrez le nom du circuit..."
                                   type="text" />
                            </div>
                            {(isSuperAdmin) &&
                                <div className="medium-auto cell">
                                    <div>
                                        <label htmlFor="collectivite_name_search">Quelle collectivité ?</label>
                                        <select id="collectivite_name_search"
                                                value={this.state.currentCollectiviteId}
                                                onChange={(event) => this.handleChangeSearchByCollectivite(event.target.value)}>
                                            {listCollectivite}
                                        </select>
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
                            <div className="cell medium-12 panel-body grid-x">
                                <div className="cell medium-6">
                                    <input type="text"
                                           placeholder="Nouveau type"
                                           name="nom"
                                           onChange={(e) => this.setState({nom: e.target.value})}
                                           value={this.state.nom} />
                                </div>
                                <div className="cell medium-6 text-right">
                                    <button className="button primary text-uppercase"
                                            onClick={() => this.createType()}>
                                        Créer un nouveau type
                                    </button>
                                </div>
                            </div>
                            {(listType.length > 0) ? listType :
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

Types.PropTypes = {
    user: object.isRequired
}

export default Types

const TypeRow = ({type, removeType, updateType, handleChangeNameFields}) => {
    return (
        <div className="cell medium-12 panel-body grid-x">
            <div className="cell medium-auto">
                <input type="text"
                       value={type.nom}
                       onChange={(e) => handleChangeNameFields(type.id, e.target.value)} />
            </div>
            <div className="cell medium-auto text-right">
                <button className="button primary text-uppercase"
                        onClick={() => updateType(type.id, type.nom)}>
                    Enregistrer
                </button>
            </div>
            {(type.supprimable) &&
                <div className="cell medium-auto text-right">
                    <button className="button alert text-uppercase"
                            onClick={() => removeType(type.id)}>
                        Supprimer
                    </button>
                </div>
            }
        </div>
    )
}

TypeRow.PropTypes = {
    type: object.isRequired,
    removeType: func.isRequired,
    updateType: func.isRequired,
    handleChangeNameFields: func.isRequired
}