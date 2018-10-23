import React, { Component } from 'react'
import {array, func, object} from 'prop-types'
import Moment from 'moment'
import { translate } from 'react-i18next'

import { Select } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'

import { handleErrors } from '../_utils/Utils'
import { escapedValue} from '../_utils/Search'
import History from '../_utils/History'
import { Intervenants} from '../_utils/Classeur'

import ClasseurPagination from './ClasseurPagination'

class ClasseursValid extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object
    }

    state = {
        classeurs: [],
        filteredClasseurs: [],
        nbClasseurChecked: 0,
        sort: "id",
        order: "DESC",
        limit: 15,
        start: 0,
        message: '',
        nbElement: 0,
        nbElementTotal: 0,
        valueSearchByTitle: ''
    }

    componentDidMount() {
        if(this.state.classeurs.length === 0) {
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
        }
    }

    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.context.user.id)
    }

    changePage = (start) => {
        const newStart = (start * this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }

    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }

    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }

    listClasseurs = (sort, order, limit, start, userId) => {
        const { t, _addNotification } = this.context
        this.setState({message: t('common.loading')})
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_valid',
                {orgId: this.context.user.current_org_id, sort, order, limit, start, userId}),
            { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                let message = null
                if(classeurs.length <= 0) message = t('common.classeurs.empty_classeur_list')
                this.setState({
                    start,
                    message,
                    classeurs,
                    filteredClasseurs: classeurs,
                    nbElement: json.nb_element_in_list,
                    nbElementTotal: json.nb_element_total_of_entity})
            })
            .catch(error => {
                this.setState({message: t('common.error_loading_list')})
                _addNotification(basicNotification(
                    'error',
                    t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                    error.statusText))
            })
    }

    handleSearchByClasseurTitle = (e) => {
        const { value } = e.target
        this.setState({valueSearchByTitle: value})
        const regex = escapedValue(value, this.state.filteredClasseurs, this.state.groups)
        const filteredClasseurs = this.state.classeurs.filter(classeur => regex.test(classeur.nom))
        this.setState({filteredClasseurs})
    }

    validateClasseur = (e, id) => {
        e.preventDefault()
        e.stopPropagation()
        fetch(Routing.generate('sesile_classeur_classeurapi_validclasseur', {id}),
            {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
            })
            .catch(error =>
                this.context._addNotification(basicNotification(
                    'error',
                    this.context.t('classeur.error.edit', {errorCode: error.status}),
                    error.statusText)))
    }

    signClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        History.push('/classeurs/previsualisation', {classeurs, user: this.context.user})
    }

    revertClasseur = (e, id) => {
        e.stopPropagation()
        fetch(Routing.generate('sesile_classeur_classeurapi_retractclasseur', {id}),
            {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
            })
            .catch(error =>
                this.context._addNotification(basicNotification(
                    'error',
                    this.context.t('classeur.error.edit', {errorCode: error.status}),
                    error.statusText)))
    }

    checkClasseur = (e, id) => {
        e.stopPropagation()
        const filteredClasseurs = this.state.filteredClasseurs
        const IndexOfClasseurInArray =
            filteredClasseurs.findIndex(classeur => classeur.id === id)
        const filteredClasseur = filteredClasseurs[IndexOfClasseurInArray]
        filteredClasseur.checked = !filteredClasseur.checked
        const nbClasseurChecked = filteredClasseurs.filter(classeur => !!classeur.checked).length
        this.setState({nbClasseurChecked, classeurs: filteredClasseurs, filteredClasseurs})
    }

    checkAllClasseur = (e) => {
        e.stopPropagation()
        const filteredClasseurs = this.state.filteredClasseurs
        filteredClasseurs.map(clas => {
            if (!!clas['signable_and_last_validant'] && !clas.checked)
                clas.checked = !clas.checked
            else if (!!clas['signable_and_last_validant'] && clas.checked)
                clas.checked = !clas.checked
        })
        const nbClasseurChecked = filteredClasseurs.filter(classeur => !!classeur.checked).length
        this.setState({nbClasseurChecked, classeurs: filteredClasseurs, filteredClasseurs})
    }

    onClickSignMultiClasseur = (e) => {
        const classeursChecked = this.state.filteredClasseurs.filter(classeur => !!classeur.checked)
        this.signClasseurs(e, classeursChecked)
    }

    render(){
        const { t } = this.context
        const { limit, start } = this.state
        const listClasseur = this.state.filteredClasseurs.map(classeur =>
            <CLasseurRow
                key={`classeur-${classeur.id}`}
                classeur={classeur}
                validateClasseur={this.validateClasseur}
                signClasseurs={this.signClasseurs}
                revertClasseur={this.revertClasseur}
                checkClasseur={this.checkClasseur}/>)
        const listLimit = [15,30,50,100].map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)

        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('common.menu.validate_classeur')}</h2>
                </div>
                <div className="cell medium-12 panel">
                    <div className="grid-x align-center-middle" style={{marginBottom: '10px'}}>
                        <div className="grid-x grid-padding-x medium-6 panel" style={{display:"flex", marginBottom:"1em", width:"50%", padding: '10px'}}>
                            {this.state.message === null ?
                                <input
                                    style={{margin: '0'}}
                                    className="cell medium-auto"
                                    value={this.state.valueSearchByTitle}
                                    onChange={(e) => this.handleSearchByClasseurTitle(e)}
                                    placeholder={"Recherche par titre"}
                                    type="text"/> :
                                <p className="text-center" style={{width: '100%'}}>
                                    {this.state.message}
                                </p>}
                        </div>
                        <div className="cell medium-12 align-right" style={{display: 'flex', marginBottom: '1em'}}>
                            <button
                                className="button hollow"
                                onClick={(e) => this.onClickSignMultiClasseur(e)}
                                disabled={this.state.nbClasseurChecked <= 1}>
                                {t('common.sign_classeur_plural')}
                            </button>
                        </div>
                        <div className="cell medium-12 align-right">
                            <table>
                                <thead style={{background: "#3199cc", color: "#fefefe"}}>
                                    <tr>
                                        <th style={{borderRadius: "0.5rem 0 0 0"}}>
                                            {t('common.label.title')}
                                        </th>
                                        <th>
                                            {t('common.stakeholders')}
                                        </th>
                                        <th>
                                            {t('common.classeurs.sort_label.limit_date')}
                                        </th>
                                        <th>
                                            {t('common.classeurs.sort_label.type')}
                                        </th>
                                        <th className="text-right">
                                            {t('common.classeurs.sort_label.actions')}
                                        </th>
                                        <th className="text-center" style={{borderRadius: "0 0.5rem 0 0"}}>
                                            <div className="pretty p-default p-curve p-thick" onClick={(e) => this.checkAllClasseur(e)}>
                                                <input
                                                    onChange={(e) => {e.stopPropagation()}}
                                                    type="checkbox"/>
                                                <div className="state p-info-o">
                                                    <label/>
                                                </div>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {this.state.message === null &&
                                        listClasseur}
                                </tbody>
                                {this.state.filteredClasseurs && (
                                    <tfoot style={{backgroundColor: "#fefefe"}}>
                                        <tr>
                                            <td>
                                                <Select
                                                    id="limit"
                                                    style={{
                                                        height: '80%',
                                                        margin: '0',
                                                        paddingBottom: '0',
                                                        paddingTop: '0',
                                                        width: '65px'
                                                    }}
                                                    value={this.state.limit}
                                                    className="cell small-4 medium-2 large-2"
                                                    onChange={this.changeLimit}
                                                    children={listLimit}/>
                                            </td>
                                            <td/>
                                            <td/>
                                            <td/>
                                            <td/>
                                            <td className="float-right">
                                                <ClasseurPagination
                                                    limit={limit}
                                                    start={start}
                                                    nbElement={this.state.nbElement}
                                                    nbElementTotal={this.state.nbElementTotal}
                                                    currentOrgId={this.context.user.current_org_id}
                                                    changeLimit={this.changeLimit}
                                                    changePreviousPage={this.changePreviousPage}
                                                    changeNextPage={this.changeNextPage}
                                                    changePage={this.changePage}/>
                                            </td>
                                        </tr>
                                    </tfoot>)}
                            </table>
                        </div>
                        <div className="cell medium-12 align-right" style={{display: 'flex'}}>
                            <button
                                className="button hollow"
                                onClick={(e) => this.onClickSignMultiClasseur(e)}
                                disabled={this.state.nbClasseurChecked <= 1}>
                                {t('common.sign_classeur_plural')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseursValid)

const CLasseurRow = ({classeur, validateClasseur, signClasseurs, revertClasseur, checkClasseur}) =>
    <tr key={classeur.id} onClick={() => History.push(`/classeur/${classeur.id}`)} style={{cursor:"Pointer"}}>
        <td className="text-bold">{classeur.nom}</td>
        <td>
            <Intervenants classeur={classeur}/>
        </td>
        <td>{Moment(classeur.validation).format('L')}</td>
        <td>{classeur.type.nom}</td>
        <td className="text-right">
            <ButtonValidate id={classeur.id} validateClasseur={validateClasseur} enabled={classeur.validable}/>
            <ButtonSignature classeur={classeur} signClasseurs={signClasseurs} enabled={classeur['signable_and_last_validant']}/>
        </td>
        <td className="text-center">
            <div
                className="pretty p-default p-curve p-thick"
                onClick={(e) => classeur['signable_and_last_validant'] && checkClasseur(e, classeur.id)}>
                <input
                    value={classeur.checked}
                    checked={classeur.checked}
                    onChange={(e) => {e.stopPropagation()}}
                    disabled={!classeur['signable_and_last_validant']}
                    type="checkbox"/>
                <div className="state p-primary-o">
                    <label/>
                </div>
            </div>
        </td>
    </tr>

const ButtonValidate = ({id, validateClasseur, enabled}, {t}) => {
    return(
        enabled ?
            <a onClick={(e) => validateClasseur(e, id)}
               title={t('common.classeurs.button.valid_title')}
               className="fa fa-check success hollow"/> :
            <i title={t('common.classeurs.button.valid_title')}
               className="fa fa-check disabled hollow"/>
    )
}

ButtonValidate.contextTypes = {
    t: func
}

const ButtonSignature = ({classeur, signClasseurs, enabled}, {t}) => {
    return (
        enabled ?
            <a title={t('common.classeurs.button.sign_title')}
               className="fa fa-edit success hollow"
               style={{marginLeft: '10%'}}
               onClick={(e) => signClasseurs(e, [classeur])} /> :
            <i title={t('common.classeurs.button.sign_title')}
               style={{marginLeft: '10%'}}
               className="fa fa-edit disabled hollow" />
    )
}

ButtonSignature.contextTypes = {
    t: func
}