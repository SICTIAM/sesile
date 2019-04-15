import React, { Component } from 'react'
import { func, object } from 'prop-types'
import Moment from 'moment'
import { translate } from 'react-i18next'

import { Select } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'

import {handleErrors} from '../_utils/Utils'
import {escapedValue} from '../_utils/Search'
import History from '../_utils/History'
import { Intervenants, StatusLabel } from '../_utils/Classeur'

import ClasseurPagination from '../classeur/ClasseurPagination'
import Debounce from "debounce"

class UserListClasseurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
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
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.props.match.params.userId)
        }
    }

    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.props.match.params.userId, this.state.valueSearchByTitle)
    }

    changePage = (start) => {
        const newStart = (start * this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.props.match.params.userId, this.state.valueSearchByTitle)
    }

    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.props.match.params.userId, this.state.valueSearchByTitle)
    }

    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.props.match.params.userId, this.state.valueSearchByTitle)
    }

    listClasseurs = Debounce((sort, order, limit, start, userId, name) => {
        const { t, _addNotification } = this.context
        if (name === "" || name === undefined) name = "null"
        const type = "null"
        const status = "null"
        this.setState({message: t('common.loading')})
        fetch(
            Routing.generate(
                'sesile_classeur_classeurapi_listsorted',
                {orgId: this.props.match.params.collectiviteId, sort, order, limit, start, userId, name, type, status}),
            {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list
                let message = null
                this.setState({})
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
    }, 500)

    handleSearchByClasseurTitle = (e) => {
        const { value } = e.target
        this.setState({valueSearchByTitle: value})
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.props.match.params.userId, value)
    }

    render(){
        const { t } = this.context
        const { limit, start } = this.state
        const listClasseur = this.state.filteredClasseurs.map(classeur =>
            <CLasseurRow key={`classeur-${classeur.id}`} classeur={classeur} collectiviteId={this.props.match.params.collectiviteId}/>)
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
                                                    currentOrgId={this.props.match.params.collectiviteId}
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

export default translate(['sesile'])(UserListClasseurs)

const CLasseurRow = ({classeur, collectiviteId}) =>
    <tr key={classeur.id} onClick={() => History.push(`/admin/${collectiviteId}/classeur/${classeur.id}`)} style={{cursor:"Pointer"}}>
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