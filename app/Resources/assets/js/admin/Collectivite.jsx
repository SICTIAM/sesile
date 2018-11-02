import React, { Component } from 'react'
import { func, number, object } from 'prop-types'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { basicNotification } from '../_components/Notifications'
import { AdminDetails, AccordionContent } from '../_components/AdminUI'
import CollectiviteInfos from './CollectiviteInfos'
import CollectiviteEmailModels from './CollectiviteEmailModels'
import CollectiviteVisa from './CollectiviteVisa'
import CollectiviteSignature from './CollectiviteSignature'

class Collectivite extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }
    
    state = {
        collectivite: {
            id: 0,
            nom: '',
            image: '',
            delete_classeur_after: 0,
            active: false
        },
        valid: [],
        refus: [],
        news: [],
        suggestion:false,
        editState: false
    }

    componentDidMount() {
        this.fetchTemplate()
        this.fetchCollectivite(this.props.match.params.collectiviteId)
        $("#admin-details").foundation()
    }

    fetchCollectivite(id) {
        fetch(Routing.generate('sesile_main_collectiviteapi_getbyid', {id}), {credentials: 'same-origin'})
        .then(handleErrors)
        .then(response => response.json())
        .then(json => this.setState({collectivite: json}))
        .catch(error => this.context._addNotification(basicNotification(
            'error',
            this.context.t('admin.collectivite.error.fetch', {errorCode: error.status}),
            error.statusText)))
    }

    handleChangeCollectiviteValue = (name, value) => {
        this.setState({editState: true})
        const { collectivite } = this.state
        collectivite[name] = value
        this.setState({collectivite})
    }


    fetchTemplate = () => {
        fetch(Routing.generate('sesile_main_collectiviteapi_getemailtemplate'), {
            method: 'GET'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({news:json[0], valid: json[1], refus:json[2]}))
    }

    putCollectivite = (id, fields) => {
        fetch(Routing.generate('sesile_main_collectiviteapi_updatecollectivite', {id}), {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(json => {
            this.context._addNotification(basicNotification(
                'success',
                this.context.t('admin.collectivite.success_edit')))
            History.push(this.props.match.url)
        })
        .catch(error => this.context._addNotification(basicNotification(
            'error',
            this.context.t('admin.collectivite.error.fail_edit', {errorCode: error.status}),
            error.statusText)))
    }
    
    render() {
        const { t } = this.context
        const { collectivite, editState, valid, refus, news } = this.state
        return (
            <AdminDetails   title={t('admin.details.title', {name: t('admin.collectivite.name'), context: 'female'})}
                            subtitle={t('admin.details.subtitle')}
                            nom={collectivite.nom}>
                <AccordionContent>
                    <CollectiviteInfos  id={collectivite.id} 
                                        nom={collectivite.nom} 
                                        delete_classeur_after={collectivite.delete_classeur_after} 
                                        image={collectivite.image} 
                                        active={collectivite.active} 
                                        handleChange={this.handleChangeCollectiviteValue} 
                                        putCollectivite={this.putCollectivite}
                                        editState={editState}/>

                    <CollectiviteEmailModels    collectivite={collectivite} 
                                                editState={editState}
                                                valid={valid}
                                                refus={refus}
                                                news={news}
                                                handleChange={this.handleChangeCollectiviteValue}
                                                putCollectivite={this.putCollectivite}/>

                    <CollectiviteVisa   id={collectivite.id}
                                        abscisses_visa={collectivite.abscisses_visa}
                                        ordonnees_visa={collectivite.ordonnees_visa}
                                        titre_visa={collectivite.titre_visa}
                                        couleur_visa={collectivite.couleur_visa}
                                        editState={editState}
                                        putCollectivite={this.putCollectivite}
                                        handleChange={this.handleChangeCollectiviteValue} />

                    <CollectiviteSignature  id={collectivite.id}
                                            abscisses_signature={collectivite.abscisses_signature}
                                            ordonnees_signature={collectivite.ordonnees_signature}
                                            page_signature={collectivite.page_signature}
                                            editState={editState}
                                            putCollectivite={this.putCollectivite}
                                            handleChange={this.handleChangeCollectiviteValue} />
                </AccordionContent>
            </AdminDetails>
        )
    }
}

export default translate(['sesile'])(Collectivite)