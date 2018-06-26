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
        sirenValid: null,
        messageMigrationHistory: ''
    }
    validationRules = {
        siren: 'required|integer'
    }
    componentDidMount() {
        this.fetchCollectivites()
        this.fetchMigrationHistory()
    }
    fetchCollectivites = () => {
        fetch(Routing.generate('v3v4_migrate_org_list'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(collectivites => this.setState({collectivites}))
            .catch(error => console.error('erreur get collectivite', error))
    }
    checkOrgSirenAvailability = (siren) => {
        fetch(Routing.generate('v3v4_migrate_check_siren', {siren}), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(orgSirenAvailability => {
                this.setState({orgSirenAvailability, sirenAvailabel: !!orgSirenAvailability.success})
            })
            .catch(error => console.error('Erreur availability siren collectivite', error))
    }
    initMigration = () => {
        const { t, _addNotification } = this.context
        this.checkOrgSirenAvailability()
        if(this.state.sirenAvailabel) {
            fetch(Routing.generate('v3v4_migrate_init'), {
                method: 'post',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({siren: this.state.siren, orgId: this.state.currentCollectivite.id}),
                credentials: 'same-origin'
            })
                .then(handleErrors)
                .then(response => response.json())
                .then(() => {
                    _addNotification(basicNotification('success', t('common.migration_is_begin')))
                    this.fetchMigrationHistory()
                    this.setState({currentCollectivite: {}, siren: '', sirenAvailabel: null, sirenValid: null})
                })
                .catch(() => _addNotification(basicNotification('error', t('common.migration_init_error'))))
        }
    }
    fetchMigrationHistory = () => {
        const { t } = this.context
        this.setState({messageMigrationHistory: t('common.loading')})
        let refreshElement = document.getElementById("refresh")
        refreshElement.classList.add("fa-spin","disabled")
        fetch(Routing.generate('v3v4_migrate_dashboard'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(migrations => {
                this.setState({migrations})
                if(migrations.length <= 0) this.setState({messageMigrationHistory: t('common.empty_list')})
                else this.setState({messageMigrationHistory: null})
                let refreshElement = document.getElementById("refresh")
                refreshElement.classList.remove("fa-spin","disabled")
            })
            .catch(error => {
                this.setState({messageMigrationHistory: t('common.error_fetch_migration_list')})
                let refreshElement = document.getElementById("refresh")
                refreshElement.classList.remove("fa-spin","disabled")
                console.error('erreur get migration history', error)
            })
    }
    handleChange = (currentCollectivite) => this.setState({currentCollectivite})
    handleChangeSiren = (name, value) => {
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
    exportUsersToOzwillo = (orgId) => {
        fetch(Routing.generate('v3v4_migrate_users_export'), {
            method: 'post',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({orgId : orgId}),
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(() => {
            _addNotification(basicNotification(
                'success',
                t('common.export_user_is_begin')
            ))
            this.fetchMigrationHistory()
        })
        .catch(error => console.error(error))
    }
    sirenIsNotAvailabel = () => this.state.sirenAvailabel !== null && this.state.sirenAvailabel === false
    sirenIsNotValid = () => this.state.sirenValid !== null && this.state.sirenValid === false
    refreshMigrationHistory = () => {
        let refreshElement = document.getElementById("refresh")
        refreshElement.classList.add("fa-spin","disabled")
        this.fetchMigrationHistory()
    }
    render() {
        const { t } = this.context
        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.migration')}</h2>
                </div>
                <div className="cell medium-12 panel" style={{marginBottom: '50px'}}>
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12">
                            <div className="callout primary">
                                <h5><i className="fa fa-info" style={{color: 'white'}}/> Les étapes de migration:</h5>
                                <ul>
                                    <li>Renseigner le SIREN pour la collectivité sur le formulaire ci-dessous.</li>
                                    <li>Provisioner Sesile pour la collectivité depuis ozwillo.</li>
                                    <li>Resyncroniser la liste des migrations en appuyant sur ce button situé sur la liste "<i className="fa fa-refresh"/>"</li>
                                    <li>Une fois le provisionnement effectif sur sesile le button "Exporter les utilistateurs" devient actif, vous pouvez alors lancer l'export d'utilisateur.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
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
                                        <strong>{`La collectivité "${this.state.orgSirenAvailability.orgName}" sera totalement supprimée de SESILE`}</strong>
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
                                {t('common.migration_list')}
                            </h3>
                        </div>
                    </div>
                    <div className="grid-x" style={{marginBottom: '10px'}}>
                        <div className="cell medium-12 align-right">
                            <ListMigrationHistory
                                migrations={this.state.migrations}
                                exportUsersToOzwillo={this.exportUsersToOzwillo}
                                messageMigrationHistory={this.state.messageMigrationHistory}
                                refreshMigrationHistory={this.refreshMigrationHistory}/>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(Migration)

const ListMigrationHistory = ({migrations, exportUsersToOzwillo, messageMigrationHistory, refreshMigrationHistory}, {t}) => {
    const listMigration = migrations.map(migration =>
        <tr key={migration.id}>
            <td>{Moment(new Date(migration.date.date)).format('LLLL')}</td>
            <td>{migration.collectivityName}</td>
            <td>
                <MigrationStatus status={migration.status} allowExport={migration.allowExport} />
            </td>
            <td>{migration.siren}</td>
            <td>{migration.usersExported ?
                    <div
                        className={`ui success label labelStatus`}
                        style={{textAlign: 'center', width: '110px'}}>
                        {t(`common.exported`)}
                    </div> :
                    <div
                        className={`ui disabled label labelStatus`}
                        style={{textAlign: 'center', width: '110px'}}>
                        {t(`common.not_exported`)}
                    </div>}
            </td>
            <td>
                <button
                    title={migration.allowExport !== 1 ? t('common.waiting_action_on_ozwillo'): t('common.export_users')}
                    className="button hollow"
                    onClick={() => exportUsersToOzwillo(migration.collectivityId)}
                    disabled={migration.allowExport !== 1}>
                    {t('common.export_users')}
                </button>
            </td>
        </tr>
    )
    return (
        <table className="hover">
            <thead>
                <tr>
                    <th width="200">Date</th>
                    <th>Collectivité</th>
                    <th width="100">Migration</th>
                    <th width="100">SIREN</th>
                    <th width="150">Utilisateurs</th>
                    <th width="150">
                        Action
                        <i
                            id="refresh"
                            style={{marginLeft: '60px'}}
                            className="icon-action fa fa-refresh"
                            onClick={(e) => refreshMigrationHistory()}
                            aria-hidden="true"/>
                    </th>
                </tr>
            </thead>
            {listMigration.length > 0 ?
                <tbody>
                    {listMigration}
                </tbody> :
                <tfoot>
                    <tr>
                        <td/>
                        <td className="text-center">{messageMigrationHistory}</td>
                        <td/>
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

const MigrationStatus = ({status, allowExport}, {t}) => {
    const statusTranslation = Object.freeze({
        'REFUSED': 'refused',
        'EN_COURS': 'pending',
        'FINALISE': 'finished',
        'WAITING': 'waiting'
    })
    const statusColorClass = Object.freeze({
        'REFUSED': 'alert',
        'EN_COURS': 'warning',
        'FINALISE': 'success',
        'WAITING': 'waiting'
    })
    let message = ''
    if(allowExport === 0) {
        status = 'WAITING'
        message = t('common.waiting_action_on_ozwillo')
    }
    return (
        <div
            title={message}
            className={`ui ${statusColorClass[status]} label labelStatus`}
            style={{color: '#fff', textAlign: 'center', width: '80px', padding: '5px', fontSize: '0.9em'}}>
            {t(`common.classeurs.status.${statusTranslation[status]}`)}
        </div>
    )
}

MigrationStatus.contextTypes = {
    t: func
}

