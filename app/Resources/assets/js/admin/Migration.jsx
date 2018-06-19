import Moment from 'moment'
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
        migrations: [],
        currentCollectivite: {},
        siren: '',
        orgSirenAvailability: {
            success: 0,
            siren: '',
            orgName: ''
        },
        sirenAvailabel: null,
        sirenValid: null
    }
    validationRules = {
        siren: 'required|integer'
    }
    componentDidMount() {
        this.fetchCollectivites()
        this.fetchMigrationHistory()
    }
    fetchCollectivites = () => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('v3v4_migrate_org_list'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
            .catch(error => console.error('erreur get collectivite', error))
    }
    checkOrgSirenAvailability = (siren) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('v3v4_migrate_check_siren', {siren}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(orgSirenAvailability => {
                this.setState({orgSirenAvailability, sirenAvailabel: !!orgSirenAvailability.success})
            })
            .catch(error => console.error('erreur availability siren collectivite', error))
    }
    initMigration = () => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('v3v4_migrate_init'), {
            credentials: 'same-origin',
            method: 'POST',
            body: {siren: this.state.siren, orgId: this.state.currentCollectivite.id}
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('common.migration_is_begin')
                ))
            })
            .catch(error => console.log(error))
    }
    fetchMigrationHistory = () => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('v3v4_migrate_dashboard'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(migrations => this.setState({migrations}))
            .catch(error => console.error('erreur get migration history', error))
    }
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
        else if (value.length >= 9)this.setState({sirenValid: false})

        else this.setState({sirenAvailabel : null, sirenValid: null})
    }
    exportUsersToOzwillo = () => console.log("Tkt ils seront exportés ... un jour !!")
    sirenIsNotAvailabel = () => this.state.sirenAvailabel !== null && this.state.sirenAvailabel === false
    sirenIsNotValid = () => this.state.sirenValid !== null && this.state.sirenValid === false
    render() {
        const { t } = this.context
        console.log(this.state.orgSirenAvailability)
        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.migration')}</h2>
                </div>
                <div className="cell medium-12 panel" style={{marginBottom: '50px'}}>
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
                    {/* @todo make it component :begin*/}
                    {this.sirenIsNotAvailabel() &&
                        <div className="grid-x">
                            <div className="cell medium-12">
                                <div className="callout alert" data-closable>
                                    <h4>{`Le SIREN ${this.state.orgSirenAvailability.siren} est déjà utilisé par la collectivité "${this.state.orgSirenAvailability.orgName}"`}</h4>
                                    <p>
                                        {`Si vous migrez la collectivité "${this.state.currentCollectivite.nom}" avec ce siren celle-ci sera liée aux données Ozwillo de la collectivité "${this.state.orgSirenAvailability.orgName}"`}
                                    </p>
                                    <button className="close-button" aria-label="Dismiss alert" type="button" data-close>
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>}
                    {this.sirenIsNotValid() &&
                        <div className="grid-x">
                            <div className="cell medium-12">
                                <div className="callout alert" data-closable>
                                    <h4>{`Le code SIREN ${this.state.siren} n'est pas valide`}</h4>
                                    <p>
                                        {"Veuillez renseigner un code SIREN valide"}
                                    </p>
                                    <button className="close-button" aria-label="Dismiss alert" type="button" data-close>
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        </div>}
                    {/*@todo :end*/}
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12 align-right">
                            <Button
                                id="submit-infos"
                                className="cell medium-12"
                                classNameButton="float-right"
                                onClick={this.initMigration}
                                disabled={!this.state.sirenValid}
                                labelText={t('common.migrate')}/>
                        </div>
                    </div>
                </div>
                <div className="cell medium-12 panel">
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12 align-right">
                            <h3>
                                {/*@todo translation*/}
                                {"Liste des migrations"}
                            </h3>
                        </div>
                    </div>
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12 align-right">
                            <ListMigrationHistory
                                migrations={this.state.migrations}
                                exportUsersToOzwillo={this.exportUsersToOzwillo}/>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(Migration)

const ListMigrationHistory = ({migrations, exportUsersToOzwillo}, {t}) => {
    const listMigration = migrations.map(migration =>
        <tr key={migration.id}>
            <td>{Moment(new Date(migration.date.date)).format('LLLL')}</td>
            <td>{migration.collectivityName}</td>
            <td>{migration.siren}</td>
            <td>
                <MigrationStatus status={migration.status} />
            </td>
            <td>
                <Button
                    id="submit-export-users-to-ozwillo"
                    className="cell medium-12"
                    classNameButton="float-right"
                    onClick={exportUsersToOzwillo}
                    disabled={!!migration.allowExport}
                    labelText={"Exporter les utilisateurs"}/>
            </td>
        </tr>
    )
    return (
        <table className="hover">
            <thead>
                <tr>
                    <th width="200">Date</th>
                    <th>Collectivité</th>
                    <th width="100">SIREN</th>
                    <th width="100">Statut</th>
                    <th width="150">Action</th>
                </tr>
            </thead>
            {listMigration.length > 0 ?
                <tbody>
                    {listMigration}
                </tbody> :
                <tfoot>
                    <tr>
                        <td/>
                        <td className="text-center">{t('common.empty_list')}</td>
                        <td/>
                        <td/>
                        <td/>
                    </tr>
                </tfoot>}
        </table>
    )
}

ListMigrationHistory.contextTypes = {
    t: func
}

const MigrationStatus = ({status}, {t}) => {
    const statusTranslation = Object.freeze({
        'REFUSED': 'refused',
        'EN_COURS': 'pending',
        'FINALISE': 'finished'
    })
    const statusColorClass = Object.freeze({
        'REFUSED': 'alert',
        'EN_COURS': 'warning',
        'FINALISE': 'success'
    })
    return (
        <div
            className={`ui ${statusColorClass[status]} label labelStatus`}
            style={{color: '#fff', textAlign: 'center', width: '80px'}}>
            {t(`common.classeurs.status.${statusTranslation[status]}`)}
        </div>
    )
}

MigrationStatus.contextTypes = {
    t: func
}