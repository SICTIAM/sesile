import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { AdminDetails, AccordionContent, AccordionItem} from "../_components/AdminUI";
import Patchs from "./Patchs";
import Helps from "./Helps";

class HelpBoard extends Component {

    static contextTypes = {
        t: func
    }

    componentDidMount() {
        $("#admin-details").foundation()
    }

    render() {
        const { t } = this.context

        return (
            <AdminDetails title={t('common.help_board.title')} nom={t('common.help_board.title')}>
                <AccordionContent>
                    <Helps />
                    <Patchs />
                    <VideoContent />
                </AccordionContent>
            </AdminDetails>
        )
    }

}

export default translate(['sesile'])(HelpBoard)



const VideoContent = ({}, {t}) => {

    return(
        <AccordionItem title={t('common.help_board.title_video')} className="is-active" classNameChild="align-center">
            <a href="https://www.youtube.com/playlist?list=PLN4SrkP6-6UVPVG5gNnldIhNBPuni1m1w" className="fa fa-youtube ico-video" target="_blank"></a>
        </AccordionItem>
    )
}
VideoContent.contextTypes = {
    t: func
}