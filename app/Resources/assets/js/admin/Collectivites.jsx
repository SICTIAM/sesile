import React, { Component } from 'react'
import PropTypes, { object } from 'prop-types'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'

class Collectivites extends Component {
    constructor(props) {
        super(props)
         this.state = {
             collectivites: [],
             filteredCollectivites: [],
             collectiviteName: ''
         }
    }

    componentDidMount() {
        this.fetchCollectivites()
    }
    

    fetchCollectivites() {
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => this.setState({collectivites: json, filteredCollectivites: json}))
    }

    handleSearchByCollectiviteName(collectiviteName) {
        this.setState({collectiviteName})
        const regex = escapedValue(collectiviteName, this.state.filteredCollectivites, this.state.collectivites)
        const filteredCollectivites = this.state.collectivites.filter(collectivite=> regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
    }

    render() {
        const { filteredCollectivites, collectiviteName } = this.state
        const listFilteredCollectivites = filteredCollectivites.map((collectivite, key) => <CollectiviteRow key={key} collectivite={collectivite} />)
        return (
            <div className="user-group">
                <h4 className="text-center text-bold">Rechercher la collectivité</h4>
                <p className="text-center">Puis accéder aux paramétres</p>

                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x">
                            <div className="auto cell">
                                <label htmlFor="name-search-admin">Lequel ?</label>
                                <input id="name-search-admin"
                                    placeholder="Entrez le nom de la collectivite..."
                                    type="text" 
                                    value={collectiviteName}
                                    onChange={(e) => this.handleSearchByCollectiviteName(e.target.value)} />
                            </div>
                        </div>
                    </div>
                    <div className="cell medium-10 list-admin">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-4">Collecitivite</div>
                                <div className="cell medium-8">Etat</div>
                            </div>
                            {(listFilteredCollectivites.length > 0) ? listFilteredCollectivites :
                                <div className="cell medium-12 panel-body">
                                    <div className="text-center">
                                        Aucun collectivité ne correspond à votre recherche...
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

Collectivites.propTypes = {
    user: object.isRequired
}

export default Collectivites

const CollectiviteRow = ({ collectivite }) => {
    return (
        <div className="cell medium-12 panel-body grid-x row-admin" onClick={() => History.push(`collectivite/${collectivite.id}`)}>
            <div className="cell medium-4 text-uppercase">
                {collectivite.nom}
            </div>
            <div className="cell medium-8 text-uppercase">
                {(collectivite.active) ? 'Active' : 'Non active'}
            </div>
        </div>
    )
}

CollectiviteRow.propTypes = {
    collectivite: object.isRequired
}