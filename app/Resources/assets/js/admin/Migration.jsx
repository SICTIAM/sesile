import { func } from 'prop-types'
import React, { Component } from 'react'
import { translate } from 'react-i18next'
import Select from 'react-select'

import InputValidation from '../_components/InputValidation'
import { Button } from '../_components/Form'
import { basicNotification } from "../_components/Notifications"

import { isEmptyObject, handleErrors, isValidSiren } from "../_utils/Utils"

class Migration extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        collectivites: [],
        currentCollectivite: {},
        siren: '',
        orgSirenAvailability: {
            success: 0,
            siren: '',
            orgName: ''
        },
        sirenAvailabel: null,
        sirenValid: false
    }
    validationRules = {
        siren: 'required|integer'
    }
    componentDidMount() {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('sesile_migration_migrationapi_getcollectivitylist'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
            .catch(error => console.error('erreur get collectivite', error))
    }
    checkOrgSirenAvailability = (siren) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('sesile_migration_migrationapi_checkorgsirenavailability', {siren}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(orgSirenAvailability => {
                this.setState({orgSirenAvailability, sirenAvailabel: orgSirenAvailability.success ? true : false})
            })
            .catch(error => console.error('erreur availability siren collectivite', error))
    }
    launchMigration = () => console.log('The migration is coming soon')
    handleChange = (currentCollectivite) => this.setState({currentCollectivite})
    handleChangeSiren = (name, value) => {
        console.log(value.length)
        if(value.length <= 9) {
            this.setState({[name]: value})
        }
        if(value.length === 9 && isValidSiren(value)) {
            this.checkOrgSirenAvailability(value)
            this.setState({sirenValid: true})
        }
        else this.setState({sirenAvailabel : null})
    }
    render() {
        const { t } = this.context
        console.log(this.state.orgSirenAvailability)
        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.migration')}</h2>
                </div>
                <div className="cell medium-12 panel">
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12">
                            <label htmlFor="collectivite-select" className="text-capitalize-first-letter text-bold">
                                {t('admin.collectivite.name')}
                            </label>
                            <Select
                                id="collectivite-select"
                                value={this.state.currentCollectivite}
                                placeholder={t('common.research')}
                                valueKey="domain"
                                labelKey="nom"
                                options={this.state.collectivites}
                                onChange={this.handleChange}/>
                        </div>
                    </div>
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12">
                            <InputValidation
                                id="siren"
                                type="text"
                                labelText={t('common.siren')}
                                disabled={!!isEmptyObject(this.state.currentCollectivite)}
                                value={this.state.siren}
                                validationRule={this.validationRules.siren}
                                onChange={this.handleChangeSiren}
                                placeholder={t('common.type_siren_collectivite')}/>
                        </div>
                    </div>
                    {(this.state.sirenAvailabel !== null && this.state.sirenAvailabel === false) &&
                        <div className="grid-x">
                            <div className="cell medium-12">
                                <div className="callout alert" data-closable>
                                    <h4>{`Le SIREN ${this.state.orgSirenAvailability.siren} est déjà utilisé par la collectivité "${this.state.orgSirenAvailability.orgName}"`}</h4>
                                    <p>
                                        {"Si vous migrez avec ce siren"}
                                    </p>
                                    <button className="close-button" aria-label="Dismiss alert" type="button" data-close>
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>}
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12 align-right">
                            <Button
                                id="submit-infos"
                                className="cell medium-12"
                                classNameButton="float-right"
                                onClick={this.launchMigration}
                                disabled={!this.state.sirenValid}
                                labelText={t('common.migrate')}/>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(Migration)