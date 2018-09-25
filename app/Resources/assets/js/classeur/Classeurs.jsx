import React, { Component } from 'react'
import { string, number, func, object }from 'prop-types'
import { translate } from 'react-i18next'

import { basicNotification } from '../_components/Notifications'
import {Input, Select} from '../_components/Form'

import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { refusClasseur, actionClasseur } from '../_utils/Classeur'

import ClasseursRow from './ClasseursRow'
import ClasseurPagination from './ClasseurPagination'
import ClasseursButtonList from './ClasseursButtonList'
import Moment from "moment"
import {escapedValue} from "../_utils/Search"

class Classeurs extends Component {

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
        checkedAll: false,
        message: '',
        nbElement: 0,
        nbElementTotal: 0,
        classeurTitle: ''
    }
    statesCaption = [
        {color: '#c82d2e', state: 'refused'},
        {color: '#f48c4f', state: 'pending'},
        {color: '#39922c', state: 'finished'},
        {color: '#1c43a2', state: 'withdrawn'},
        {color: '#356bfc', state: 'retracted'}
    ]
    componentDidMount() {
        if(this.state.classeurs.length === 0) {
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
        }
    }
    componentWillReceiveProps(nextProps) {

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
        fetch(Routing.generate(this.props.url, {orgId: this.context.user.current_org_id, sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                this.setState({classeurs, filteredClasseurs: classeurs, nbElement: json.nb_element_in_list, nbElementTotal: json.nb_element_total_of_entity})
                $('#classeurRow').foundation()
                if(classeurs.length <= 0) this.setState({message: t('common.classeurs.empty_classeur_list')})
                else this.setState({message: null})
            })
            .then(this.setState({start}))
            .catch(error => {
                this.setState({message: t('common.error_loading_list')})
                _addNotification(basicNotification(
                    'error',
                    t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                    error.statusText))
            })
    }
    checkAllClasseurs = () => {
        const newCheckAll = !this.state.checkedAll
        this.setState({checkedAll: newCheckAll})
        this.setState(prevState => prevState.classeurs.map(classeur => classeur.checked = newCheckAll))
    }
    checkClasseur = (event) => {
        event.preventDefault()
        event.stopPropagation()
        const target = event.target
        const filteredClasseurs = this.state.filteredClasseurs
        const IndexOfClasseurInArray =
            filteredClasseurs.findIndex(classeur => classeur.id == target.id)
        const filteredClasseur = filteredClasseurs[IndexOfClasseurInArray]
        filteredClasseur.checked = !filteredClasseur.checked
        this.setState({classeurs: filteredClasseurs, filteredClasseurs})
    }
    validClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_validclasseur', classeur.id, 'PUT', 'list')})
    }
    signClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        History.push('/classeurs/previsualisation', {classeurs, user: this.context.user})
    }
    revertClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_retractclasseur', classeur.id, 'PUT', 'list')})}
    refuseClasseurs = (e, classeurs, motif) => {
        e.preventDefault()
        e.stopPropagation()
        classeurs.map(classeur => {
            refusClasseur(this, 'sesile_classeur_classeurapi_refuseclasseur', classeur.id, motif, 'list') })}
    removeClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_removeclasseur', classeur.id, 'PUT', 'list') })}
    deleteClasseurs = (e, classeurs) => {
        e.preventDefault()
        e.stopPropagation()
        classeurs.map(classeur => {
            actionClasseur(this, 'sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE', 'list') })}

    handleSearchByClasseurTitle = (target) => {
        const {name, value} = target
        this.setState({classeurTitle: value})
        const regex = escapedValue(value, this.state.filteredClasseurs, this.state.groups)
        const filteredClasseurs = this.state.classeurs.filter(classeur => regex.test(classeur.nom))
        this.setState({filteredClasseurs})
    }

    render(){
        const { classeurs, limit, start, checkedAll } = this.state
        const { t } = this.context
        const limits = [15,30,50,100]
        const status = Object.freeze({
            0: 'refused',
            1: 'pending',
            2: 'finished',
            3: 'withdrawn',
            4: 'retracted'
        })
        const statusColorClass = Object.freeze({
            0: '#c82d2e',
            1: '#f48c4f',
            2: '#39922c',
            3: '#2068a2',
            4: '#34a3fc'
        })
        const listClasseur = this.state.filteredClasseurs.map(classeur =>
            <tr key={classeur.id} onClick={() => History.push(`/classeur/${classeur.id}`)} style={{cursor:"Pointer"}}>
                <td>{classeur.nom}</td>
                <td>
                    <span
                        className={`ui label labelStatus`}
                        style={{color: '#fff', backgroundColor: statusColorClass[classeur.status], textAlign: 'center', width: '80px', padding: '5px', fontSize: '0.9em'}}>
                        {t(`common.classeurs.status.${status[classeur.status]}`)}
                    </span>
                </td>
                <td>
                    <Intervenants classeur={classeur}/>
                </td>
                <td>{Moment(classeur.validation).format('L')}</td>
                <td>{classeur.type.nom}</td>
                <td>
                    <ClasseursButtonList
                        classeurs={[classeur]}
                        validClasseur={this.validClasseurs}
                        signClasseur={this.signClasseurs}
                        revertClasseur={this.revertClasseurs}
                        refuseClasseur={this.refuseClasseurs}
                        removeClasseur={this.removeClasseurs}
                        deleteClasseur={this.deleteClasseurs}
                        id={classeur.id}
                        user={this.props.user}
                        check={this.checkClasseur}
                        checked={classeur.checked}
                        style={{fontSize: '0.8em'}}/>
                </td>
            </tr>)
        const listLimit = limits.map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)
        return (
            <div className="grid-x align-center-middle" style={{marginBottom: '10px'}}>
                <div className="grid-x grid-padding-x medium-6 panel" style={{display:"flex", marginBottom:"1em", width:"50%", padding: '10px'}}>
                    <input
                        style={{margin: '0'}}
                        className="cell medium-auto"
                        value={this.state.classeurTitle}
                        onChange={(e) => this.handleSearchByClasseurTitle(e.target)}
                        placeholder={"Recherche par titre"}
                        type="text"/>
                </div>
                <div className="cell medium-12 align-right">
                    <table>
                        <thead
                            style={{
                                background: "#3199cc",
                                color: "#fefefe"
                            }}>
                            <tr>
                                <th style={{borderRadius: "0.5rem 0 0 0"}}>Titre</th>
                                <th>Status</th>
                                <th>Intervenants</th>
                                <th>Date limite</th>
                                <th>Type</th>
                                <th width="150px" style={{borderRadius: "0 0.5rem 0 0"}}>
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="classeurRow">
                            {this.state.message === null ?
                                listClasseur :
                                <tr>
                                    <td/>
                                    <td/>
                                    <td className="text-center">
                                        {this.state.message}
                                    </td>
                                    <td/>
                                    <td/>
                                    <td/>
                                </tr>}
                        </tbody>
                        {classeurs &&
                            <tfoot style={{border: "1px solid #ccc"}}>
                                <tr>
                                    <td>
                                        <Select
                                            id="limit"
                                            style={{
                                                height: '80%',
                                                margin: '0',
                                                paddingBottom: '0',
                                                paddingTop: '0',
                                                width: '60px'
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
                                            changePage={this.changePage}
                                            url={this.props.url}/>
                                    </td>
                                </tr>
                            </tfoot>}
                        </table>
                    </div>
                </div>
        )
    }
}

Classeurs.PropTypes = {
    url: string.isRequired
}

export default translate(['sesile'])(Classeurs)


const ClasseurStateCaption = ({state, color = 'blue'}, {t}) =>
    <div
        className="align-middle"
        style={{
            marginRight: '10px',
            display: 'flex',
            textTransform: 'none',
            fontSize: '.8em',
            color: '#4a4c63'}}>
        <div
            style={{
                display: 'inline-block',
                width: '15px',
                height: '15px',
                borderRadius: '10px',
                background: `${color}`}}>
        </div>
        <span
            style={{
                marginLeft: '5px',
                display: 'inline-block'}}>
            {t(`common.classeurs.status.${state}`)}
        </span>
    </div>

ClasseurStateCaption.contextTypes = {
    t: func
}


const ListClasseur = ({classeurs}, {t}) => {

    return (
        <table className="hover">
            <thead>
            <tr>
                <th width="350">Titre</th>
                <th>Status</th>
                <th>Intervenants</th>
                <th width="100">Date limite</th>
                <th width="100">Type</th>
                <th width="150">Actions</th>
            </tr>
            </thead>
            {listClasseur.length > 0 ?
                <tbody>
                {listClasseur}
                </tbody> :
                <tfoot>
                <tr>
                    <td/>
                    <td className="text-center">{"Aucun classeur à afficher"}</td>
                    <td/>
                    <td/>
                    <td/>
                    <td/>
                </tr>
                </tfoot>}
        </table>
    )
}

ListClasseur.contextTypes = {
    t: func
}

const Intervenants = ({classeur}) => {
    const validEtape = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
    return (
        <ul className="no-bullet">{
            validEtape ?
                validEtape.users.map(user =>
                        <li
                            key={`${user._nom}-${user.id}`}
                            >
                    {user._prenom + " " + user._nom}
                </li>
                ).concat(validEtape.user_packs.map(user_pack =>
                    <li
                        key={`${user_pack._nom}-${user_pack.id}`}
                        >
                        {user_pack.nom}
                    </li>)) :
                `${classeur.user._prenom} ${classeur.user._nom}`
        }
        </ul>
    )
}