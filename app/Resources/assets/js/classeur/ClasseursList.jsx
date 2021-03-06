import React, { Component } from 'react'
import { func, object } from 'prop-types'
import Moment from 'moment'
import { translate } from 'react-i18next'

import Select from 'react-select'
import { Select as Selection} from '../_components/Form'
import { basicNotification } from '../_components/Notifications'

import Debounce from 'debounce'
import {handleErrors} from '../_utils/Utils'
import {escapedValue} from '../_utils/Search'
import History from '../_utils/History'
import { Intervenants, StatusLabel } from '../_utils/Classeur'

import ClasseurPagination from './ClasseurPagination'

class ClasseursList extends Component {

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
        valueSearchByTitle: '',
        valueSearchByIntervenant: '',
        valueSearchByType: '',
        currentType: '',
        type: [],
        currentStatus: '',
        isSorted:false,
        isOpen: false,
    }

    componentDidMount() {
        if (this.state.classeurs.length === 0) {
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
            this.getTypes(this.context.user.current_org_id)
        }
    }

    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.state.isSorted === false ?
            this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.context.user.id)
            :
            this.listSortedClasseurs(this.state.sort, this.state.order, value, this.state.start, this.context.user.id, this.state.valueSearchByTitle, this.state.currentType.id, this.state.currentStatus.id)
    }

    changePage = (start) => {
        const newStart = (start * this.state.limit)
        this.state.isSorted === false ?
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
            :
            this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id, this.state.valueSearchByTitle, this.state.currentType.id, this.state.currentStatus.id)
    }

    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.state.isSorted === false ?
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
            :
            this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id, this.state.valueSearchByTitle, this.state.currentType.id, this.state.currentStatus.id)
    }

    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.state.isSorted === false ?
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
            :
            this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id, this.state.valueSearchByTitle, this.state.currentType.id, this.state.currentStatus.id)
    }

    getTypes(id) {
        const { t, _addNotification} = this.context
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(circuits => {
                const type = circuits
                const all = {id: "null", nom:"Tout"}
                type.unshift(all)
                this.setState({type})
            })
    }

    listClasseurs = (sort, order, limit, start, userId) => {
        const {t, _addNotification} = this.context
        const name = "null"
        const type = "null"
        const status = "null"
        this.setState({message: t('common.loading')})
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_listsorted',
                {orgId: this.context.user.current_org_id, sort, order, limit, start, userId, name, type, status}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list
                let message = null
                this.setState({})
                if (classeurs.length <= 0) message = t('common.classeurs.empty_classeur_list')
                this.setState({
                    start,
                    message,
                    classeurs,
                    filteredClasseurs: classeurs,
                    nbElement: json.nb_element_in_list,
                    nbElementTotal: json.nb_element_total_of_entity
                })
            })
            .catch(error => {
                this.setState({message: t('common.error_loading_list')})
                _addNotification(basicNotification(
                    'error',
                    t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                    error.statusText))
            })
    }

    listSortedClasseurs = Debounce((sort, order, limit, start, userId, name, type, status, search) => {
        if (search) start = 0
        if (name === "") name = "null"
        if (type === undefined)  type = "null"
        if (status === undefined) status = "null"
        const {t, _addNotification} = this.context
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_listsorted',
                {orgId: this.context.user.current_org_id, sort, order, limit, start, userId, name, type, status}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list
                this.setState({
                    start,
                    classeurs,
                    isSorted:true,
                    filteredClasseurs: classeurs,
                    nbElement: json.nb_element_in_list,
                    nbElementTotal: json.nb_element_total_of_entity
                })
            })
            .catch(error => {
                this.setState({message: t('common.error_loading_list')})
                _addNotification(basicNotification(
                    'error',
                    t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                    error.statusText))
            })
    },500)

    handleSearchByClasseurTitle = (e) => {
        const {value} = e.target
        this.setState({valueSearchByTitle: value})
        this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id, value, this.state.currentType.id, this.state.currentStatus.id, true)
    }

    handleSearchByStatus = (value) => {
        if (!value)
            value = {id: "null", nom: 'Tout'}
        this.setState({currentStatus: value})
        this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id, this.state.valueSearchByTitle, this.state.currentType.id, value.id, true)
    }

    handleSearchByType = (e) => {
        if (!e)
            e = {id : "null", nom : "Tout"}
        this.setState({currentType: e})
        this.listSortedClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id, this.state.valueSearchByTitle, e.id, this.state.currentStatus.id, true)

    }
    handleDropdown = () => {
        this.setState({isOpen: !this.state.isOpen})
    }


    render() {
        const {t} = this.context
        const {limit, start} = this.state
        const listClasseur = this.state.filteredClasseurs.map(classeur => <CLasseurRow key={`classeur-${classeur.id}`}
                                                                                       classeur={classeur}/>)
        const status = [
            {"id": 'null', "nom": 'Tout'},
            {"id": 0, "nom": "Refusé"},
            {"id": 1, "nom": "En cours"},
            {"id": 2, "nom": "Finalisé"},
            {"id": 3, "nom": "Retiré"},
            {"id": 4, "nom": "Rétracté"}
            ]
        const listLimit = [15,30,50,100].map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)

        return (
            <div className="grid-x grid-margin-x grid-padding-x grid-padding-y align-center-middle">
                <div className="cell medium-12 text-center">
                    <h2>{t('common.menu.list_classeur')}</h2>
                </div>
                <div className="cell medium-12 panel">
                    <div className="grid-x align-center-middle" style={{marginBottom: '10px'}}>
                        <div className="grid-x grid-padding-x medium-6 panel"
                             style={{display: "flex", marginBottom: "1em", width: "50%", padding: '10px'}}>
                            {this.state.message === null ?
                                <div className="cell medium-12 align-center-middle" style={{display: "flex"}}>
                                    <input
                                        style={{margin: '0'}}
                                        className="cell medium-auto"
                                        value={this.state.valueSearchByTitle}
                                        onChange={(e) => this.handleSearchByClasseurTitle(e)}
                                        placeholder={"Recherche par titre"}
                                        type="text"/>
                                    <a style={{marginLeft:"-30px", color:"grey"}} className={`fa fa-angle-${this.state.isOpen ? 'up' : 'down'}`} onClick={this.handleDropdown}>
                                    </a>
                                </div> :
                                <p className="text-center" style={{width: '100%'}}>
                                    {this.state.message}
                                </p>}

                            {this.state.isOpen &&
                            <div className="cell medium-12 align-center-middle"
                                 id="example-dropdown"
                                 style={{marginTop: "10px", display: "flex"}}>
                                <div className="cell medium-12 align-center-middle"
                                     style={{width: "40%", marginRight: "20px"}}>
                                    <span>{t('common.classeurs.sort_label.status')}</span>
                                    <Select
                                        id="collectivites_select"
                                        placeholder={t('common.select_statut')}
                                        value={this.state.currentStatus}
                                        wrapperStyle={{marginBottom: "0.65em"}}
                                        valueKey="id"
                                        labelKey="nom"
                                        options={status}
                                        onChange={this.handleSearchByStatus}/>
                                </div>
                                <div className="cell medium-12 align-center-middle" style={{width: "40%"}}>
                                    <span>{t('common.classeurs.sort_label.type')}</span>
                                    <Select
                                        id="collectivite_select"
                                        placeholder={t('common.select_type')}
                                        value={this.state.currentType}
                                        wrapperStyle={{marginBottom: "0.65em"}}
                                        labelKey="nom"
                                        options={this.state.type}
                                        onChange={this.handleSearchByType}/>
                                </div>
                            </div>
                            }
                        </div>
                        <div className="cell medium-12 align-right">
                            <table>
                                <thead style={{background: "#3199cc", color: "#fefefe"}}>
                                <tr>
                                    <th style={{borderRadius: "0.5rem 0 0 0"}}>
                                        {t('common.label.title')}
                                    </th>
                                    <th className="text-capitalize-first-letter">
                                        {t('common.classeurs.status.name')}
                                    </th>
                                    <th>
                                        {t('common.stakeholders')}
                                    </th>
                                    <th>
                                        {t('common.classeurs.sort_label.limit_date')}
                                    </th>
                                    <th style={{borderRadius: "0 0.5rem 0 0"}}>
                                        {t('common.classeurs.sort_label.type')}
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
                                            <Selection
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

export default translate(['sesile'])(ClasseursList)

const CLasseurRow = ({classeur}) =>
    <tr key={classeur.id} onClick={() => History.push(`/classeur/${classeur.id}`)} style={{cursor:"Pointer"}}>
        <td className="text-bold">{classeur.nom}</td>
        <td>
            <StatusLabel status={classeur.status} />
        </td>
        <td>
            <Intervenants classeur={classeur}/>
        </td>
        <td>{Moment(classeur.validation).format('L')}</td>
        <td>{classeur.type.nom}</td>
    </tr>