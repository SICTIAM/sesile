import React, {Component} from 'react'
import { array, string, bool, func } from 'prop-types'
import { Link } from 'react-router-dom'
import ClasseursButtonList from '../classeur/ClasseursButtonList'
import ClasseurProgress from '../classeur/ClasseurProgress'
import { translate } from 'react-i18next'
import History from "../_utils/History";

class Classeurs extends Component {

    static contextTypes = {
        t: func
    }
    static defaultProps = {
        classeurs : []
    }

    render () {

        const { classeurs, title } = this.props
        const { t } = this.context
        const listItems = classeurs.map((classeur, key) =>
            <tr id="classrow"
                key={key}
                onClick={() => History.push(`/classeur/${classeur.id}`)} style={{cursor:"Pointer", fontSize:"0.9em"}}>
                <td className="text-bold">
                    { classeur.nom }
                </td>
                <td className="text-justify">
                    {classeur.type.nom}
                </td>
                <td className="text-justify">
                    <ClasseurProgress creation={classeur.creation} validation={classeur.validation} status={classeur.status} />
                </td>
            </tr>
        );

        return (
            <div className="grid-x grid-padding-x panel">
                <div className="cell medium-12">
                    <div className=" align-middle " style={{paddingTop:'0.5em'}}>
                        <h3>{ title }</h3>
                    </div>
                    <table>
                        <thead>
                            <tr style={{backgroundColor:"#3299cc", color:"white"}}>
                                <td width="290px" className="text-bold">{ t('common.classeurs.sort_label.name') }</td>
                                <td width="110px" className="text-bold">{ t('common.classeurs.sort_label.type') }</td>
                                <td width="120px" className="text-bold">{ t('common.classeurs.sort_label.limit_date') }</td>
                            </tr>
                        </thead>
                        <tbody>
                    {listItems.length > 0 ?
                        listItems :
                        <tr>
                            <td>
                                <span style={{textAlign:"center"}}>{this.props.message}</span>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>}
                        </tbody>
                    </table>
                </div>

            </div>
        )
    }
}

Classeurs.propTypes = {
    classeurs: array.isRequired,
    title: string
}

export default translate('sesile')(Classeurs)