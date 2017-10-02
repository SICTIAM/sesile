import React, { Component } from 'react'
import PropTypes, { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'
import { basicNotification } from '../_components/Notifications'

class Collectivites extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
         this.state = {
             collectivites: [],
             filteredCollectivites: [],
             collectiviteName: ''
         }
    }

    handleErrors(response) {
        if (response.ok) {
            return response
        }
        throw response  
    }

    componentDidMount() {
        this.fetchCollectivites()
    }
    

    fetchCollectivites() {
        const { t } = this.context
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
        .then(this.handleErrors)
        .then(response => response.json())
        .then(json => this.setState({collectivites: json, filteredCollectivites: json}))
        .catch(error => this.context._addNotification(basicNotification('error', 
                                                                        t('admin.error.not_extrayable_list', 
                                                                        {name: t('admin.collectivite.name', {count: 2}), errorCode: error.status}), 
                                                                        error.statusText)))
    }

    handleSearchByCollectiviteName(collectiviteName) {
        this.setState({collectiviteName})
        const regex = escapedValue(collectiviteName, this.state.filteredCollectivites, this.state.collectivites)
        const filteredCollectivites = this.state.collectivites.filter(collectivite=> regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
    }

    render() {
        const { t } = this.context
        const { filteredCollectivites, collectiviteName } = this.state
        const listFilteredCollectivites = filteredCollectivites.map((collectivite, key) => <CollectiviteRow key={key} collectivite={collectivite} />)
        return (
            <div className="user-group">
                <h4 className="text-center text-bold">{t('admin.collectivite.title')}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>

                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x">
                            <div className="auto cell">
                                <label htmlFor="name-search-admin">{t('admin.collectivite.which')}</label>
                                <input id="name-search-admin"
                                    placeholder={t('admin.collectivite.type_name')}
                                    type="text" 
                                    value={collectiviteName}
                                    onChange={(e) => this.handleSearchByCollectiviteName(e.target.value)} />
                            </div>
                        </div>
                    </div>
                    <div className="cell medium-10 list-admin">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-4">{t('admin.collectivite.name')}</div>
                                <div className="cell medium-8">{t('admin.collectivite.state')}</div>
                            </div>
                            {(listFilteredCollectivites.length > 0) ? listFilteredCollectivites :
                                <div className="cell medium-12 panel-body">
                                    <div className="text-center">
                                        {t('admin.collectivite.no_results')}
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

export default translate(['sesile'])(Collectivites)

const CollectiviteRow = ({ collectivite }, {t}) => {
    return (
        <div className="cell medium-12 panel-body grid-x row-admin" onClick={() => History.push(`collectivite/${collectivite.id}`)}>
            <div className="cell medium-4 text-uppercase">
                {collectivite.nom}
            </div>
            <div className="cell medium-6 text-uppercase">
                {(collectivite.active) ? t('common.label.enabled') : t('common.label.disabled')}
            </div>
        </div>
    )
}

CollectiviteRow.propTypes = {
    collectivite: object.isRequired
}

CollectiviteRow.contextTypes = {
    t: func 
}