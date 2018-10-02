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

class ClasseursRetract extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object
    }

    state = {
        classeurs: [],
        filteredClasseurs: [],
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
                'sesile_classeur_classeurapi_listretract',
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
                this.context._addNotification(
                    basicNotification(
                        'success',
                        this.context.t('common.workbook_retracted')))
                this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
            })
            .catch(error =>
                this.context._addNotification(basicNotification(
                    'error',
                    this.context.t('classeur.error.edit', {errorCode: error.status}),
                    error.statusText)))
    }
    
    render(){
        const { t } = this.context
        const { limit, start } = this.state
        const listClasseur = this.state.filteredClasseurs.map(classeur =>
            <CLasseurRow
                key={`classeur-${classeur.id}`}
                classeur={classeur}
                revertClasseur={this.revertClasseur}/>)
        const listLimit = [15,30,50,100].map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)

        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('common.menu.retractable_classeur')}</h2>
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
                                        <th className="text-right" style={{borderRadius: "0 0.5rem 0 0", marginRight: '10px'}}>
                                            {t('common.classeurs.sort_label.actions')}
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
                    </div>
                </div>
            </div>
        )
    }
}

export default translate(['sesile'])(ClasseursRetract)

const CLasseurRow = ({classeur, revertClasseur}) =>
    <tr key={classeur.id} onClick={() => History.push(`/classeur/${classeur.id}`)} style={{cursor:"Pointer"}}>
        <td className="text-bold">{classeur.nom}</td>
        <td>
            <Intervenants classeur={classeur}/>
        </td>
        <td>{Moment(classeur.validation).format('L')}</td>
        <td>{classeur.type.nom}</td>
        <td className="text-right">
            <ButtonRevert id={classeur.id} revertClasseur={revertClasseur} enabled={classeur.retractable}/>
        </td>
    </tr>

const ButtonRevert = ({id, revertClasseur, enabled}, {t}) => {
    return(
        enabled ?
            <a onClick={(e) => revertClasseur(e, id)}
               title={t('common.classeurs.button.revert_title')}
               style={{marginRight: '10%'}}
               className="fa fa-repeat warning hollow"/> :
            <i title={t('common.classeurs.button.revert_title')}
               style={{marginRight: '10%'}}
               className="fa fa-repeat disabled hollow"/>
    )
}

ButtonRevert.contextTypes = {
    t: func
}