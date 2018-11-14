import React, { Component } from 'react'
import { number, array, func, object } from 'prop-types'
import Debounce from 'debounce'
import { translate } from 'react-i18next'
import History from '../_utils/History'
import Select from 'react-select'
import { handleErrors } from '../_utils/Utils'
import { ButtonConfirm } from '../_components/Form'
import { GridX, Cell } from '../_components/UI'
import { basicNotification } from '../_components/Notifications'
import {AdminDetailsWithInputField, AdminPage, SimpleContent} from '../_components/AdminUI'
import InputValidation from "../_components/InputValidation";
import UsersCopy from "../classeur/UsersCopy";
import Validator from "validatorjs";

class Group extends Component {

    static contextTypes = {
        t: func,
        user: object,
        _addNotification: func
    }

    constructor(props) {
        super(props)

        this.state = {
            group: {
                id: '',
                nom: '',
                users: []
            },
            collectiviteId: '',
            users: [],
            users_collectivite: [],
            user_option : [],
            selectGroup:[],
            inputDisplayed: false,
            inputSearchUser: ''
        }
    }
    validationRules = {
        title: 'required|string',
     }
    componentDidMount() {
        $(document).foundation()
        this.fetchUsersCollectivite()
        const { collectiviteId, groupId } = this.props.match.params
        this.setState({collectiviteId})
        if(!!groupId) this.getGroup(groupId)
    }

    handleChangeGroupName = (key, value) => {
        const group = this.state.group
        group.nom = value
        this.setState({group})
    }

    handleClickUser = (users) => {
        const { group } = this.state
        let groupe = group
        let users_group = []
        users.map((user) => {
            const index = this.state.users_collectivite.findIndex(users_collectivite => `${users_collectivite.id}` === user.value)
            users_group.push(this.state.users_collectivite[index])
        })
        groupe.users = users_group
        this.setState({selectGroup:users, group: groupe})
    }

    handleClickSave = () => {
        const { group, collectiviteId } = this.state
        const fields = {
            nom: group.nom,
            collectivite: collectiviteId,
            users: group.users.map(user => user.id)
        }
        if(fields.users.length >= 2) {
            if(group.id) this.putGroup(group.id, fields) 
            else this.postGroup(fields)
        }
    }

    handleClickDelete = () => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate("sesile_user_userpackapi_remove", {id: this.state.group.id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => { _addNotification(basicNotification(
            'success',
            t('admin.group.success_delete')))
            History.push(`/admin/groupes`)})
        .catch(error => _addNotification(basicNotification(
            'error',
            t('admin.error.not_removable', {name:t('admin.group.complet_name'), errorCode: error.status}),
            error.statusText)))
    }

    postGroup = (fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_postuserpack"), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(response => {if(response.ok === true) History.push(`/admin/groupes`)})
    }

    putGroup = (id, fields) => {
        fetch(Routing.generate("sesile_user_userpackapi_updateuserpack", {id}), {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(fields),
            credentials: 'same-origin'
        })
        .then(response => {if(response.ok === true) History.push(`/admin/groupes`)})
    }

    addUser = () => {
        this.setState({inputDisplayed: true})
    }

    fetchUsersCollectivite() {
        fetch(Routing.generate('sesile_user_userapi_userscollectivite', {id: this.context.user.current_org_id}) , { credentials: 'same-origin'})
            .then(response => response.json())
            .then((users_collectivite) => {
                this.setState({users_collectivite : users_collectivite})

            })
            .then(() => {
                let groups = []
                this.state.users_collectivite.map((group) => {
                    const user = {label:`${group._prenom} ${group._nom}`, value:`${group.id}`}
                    groups.push(user)
                })
                this.setState({user_option: groups})
            })
    }


    getGroup(id) {
        fetch(Routing.generate("sesile_user_userpackapi_getbyid", {id}), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => { this.setState({group: json})})
            .then(() => {
                let groups = []
                this.state.group.users.map((group) => {
                    const user = {label:`${group._prenom} ${group._nom}`, value:`${group.id}`}
                    groups.push(user)
                    })
                this.setState({selectGroup:groups})
            })
    }

    findUser = Debounce((value, collectiviteId) => {
        fetch(Routing.generate("sesile_user_userapi_findbynomorprenom", {value,collectiviteId}), {credentials: 'same-origin'})
        .then(response => response.json())
        .then(json => {
            let users = json
            this.state.group.users.map(userGroup => users = users.filter(user => user.id !== userGroup.id))
            this.setState({users})
        })
    }, 800)

    render() {
        const { t } = this.context
        const { group, inputDisplayed, inputSearchUser, users, value, suggestions, validator } = this.state
        return (
            <AdminPage  className="parameters-user-group"
                        title={t('admin.group.complet_name')}>
                <SimpleContent className="panel">
                    <div className="grid-x grid-margin-x">
                        <Cell>
                            <InputValidation
                                id="nomName"
                                type="text"
                                autoFocus={true}
                                labelText={t('common.label.name')}
                                value={group.nom}
                                validationRule={this.validationRules.title}
                                onChange={this.handleChangeGroupName}
                                placeholder={t('admin.placeholder.name', {name: t('admin.group.name')})}/>
                        </Cell>
                        <div className="cell" style={{marginBottom: "1em"}}>
                            <div className="cell text-bold text-capitalize-first-letter">
                                {t("admin.users_list")}
                            </div>
                            <Select id="users_copy_select"
                                    value={this.state.selectGroup}
                                    multi
                                    placeholder={t('common.classeurs.users_copy_select')}
                                    options={this.state.user_option}
                                    onChange={this.handleClickUser}
                            />
                        </div>
                        <Cell>
                            <GridX className="grid-margin-x">
                                <ButtonConfirm  id="confirm-delete"
                                                className="cell medium-9 text-right"
                                                handleClickConfirm={this.handleClickDelete}
                                                labelButton={t('common.button.delete')}
                                                confirmationText={"Voulez-vous le supprimer ?"}
                                                labelConfirmButton={t('common.button.confirm')}/>
                                <Cell className="medium-3">
                                    <button className="button hollow float-right text-uppercase"
                                            onClick={() => this.handleClickSave()}>
                                            {(!group.id) ? t('admin.button.save', {name: t('admin.group.name')}) : t('common.button.edit_save')}
                                    </button>
                                </Cell>
                            </GridX>
                        </Cell>
                    </div>
                </SimpleContent>
            </AdminPage>
        )
    }
}

Group.PropTypes = {
    match: object.isRequired
}

export default translate(['sesile'])(Group)

const ListSearchUser = ({ users, onClick }) => {
    const list = users.map((user, key) => <li className="list-group-item" onClick={() => onClick(user)} key={key}>{user._prenom + ' ' + user._nom}</li>)
    return (
        <div className="list-autocomplete">
            <ul className="list-group">
                {list}
            </ul>
        </div>
    )
}

ListSearchUser.PropTypes = {
    users: array.isRequired,
    onClick: func.isRequired
}